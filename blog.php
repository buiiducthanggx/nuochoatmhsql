<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

function fetch_community_messages(int $limit = 80): array
{
    $stmt = db()->prepare('SELECT m.id, m.message, m.created_at, m.customer_id, c.full_name, c.role, c.avatar_path FROM community_messages m JOIN customers c ON c.id = m.customer_id ORDER BY m.id DESC LIMIT :limit');
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return array_reverse($stmt->fetchAll());
}

function render_community_messages(array $messages, ?int $viewerId): string
{
    ob_start();
    foreach ($messages as $message):
        $isOwn = $viewerId !== null && (int)$message['customer_id'] === $viewerId;
        $author = trim((string)($message['full_name'] ?? 'Thành viên'));
        $role = (string)($message['role'] ?? 'customer');
        ?>
        <div class="chat-item <?= $isOwn ? 'own' : '' ?>" data-id="<?= (int)$message['id'] ?>">
            <div class="chat-meta">
                <?php if ($avatarUrl = user_avatar_url(['avatar_path' => (string)($message['avatar_path'] ?? '')])): ?>
                    <img class="chat-avatar" src="<?= e($avatarUrl) ?>" alt="Avatar">
                <?php else: ?>
                    <span class="chat-avatar-fallback"><?= e(user_initial($author)) ?></span>
                <?php endif; ?>
                <div class="chat-meta-text">
                    <strong><?= e($author) ?></strong>
                    <?php if ($role === 'admin'): ?><span class="chat-badge">Admin</span><?php endif; ?>
                    <small><?= e((string)$message['created_at']) ?></small>
                </div>
            </div>
            <div class="chat-bubble"><?= nl2br(e((string)$message['message'])) ?></div>
        </div>
    <?php
    endforeach;
    return (string)ob_get_clean();
}

$user = current_user();
$viewerId = $user ? (int)$user['id'] : null;
$isAjaxRequest = (string)($_GET['ajax'] ?? '') === '1' || strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';

if (is_post() && (string)($_POST['action'] ?? '') === 'send_message') {
    require_login();

    $message = trim((string)($_POST['message'] ?? ''));
    if ($message === '') {
        if ($isAjaxRequest) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['ok' => false, 'error' => 'Vui lòng nhập nội dung tin nhắn.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $_SESSION['flash_error'] = 'Vui lòng nhập nội dung tin nhắn.';
        redirect('blog.php');
    }
    if (mb_strlen($message) > 1000) {
        if ($isAjaxRequest) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['ok' => false, 'error' => 'Tin nhắn quá dài. Tối đa 1000 ký tự.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $_SESSION['flash_error'] = 'Tin nhắn quá dài. Tối đa 1000 ký tự.';
        redirect('blog.php');
    }

    $stmt = db()->prepare('INSERT INTO community_messages(customer_id, message) VALUES(:customer_id, :message)');
    $stmt->execute([
        'customer_id' => (int)$user['id'],
        'message' => $message,
    ]);

    if ($isAjaxRequest) {
        $messages = fetch_community_messages();
        $lastId = 0;
        if ($messages) {
            $last = $messages[count($messages) - 1];
            $lastId = (int)$last['id'];
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'ok' => true,
            'html' => render_community_messages($messages, $viewerId),
            'last_id' => $lastId,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    redirect('blog.php');
}

if ((string)($_GET['ajax'] ?? '') === '1') {
    $messages = fetch_community_messages();
    $lastId = 0;
    if ($messages) {
        $last = $messages[count($messages) - 1];
        $lastId = (int)$last['id'];
    }

    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'html' => render_community_messages($messages, $viewerId),
        'last_id' => $lastId,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$messages = fetch_community_messages();
$initialLastId = 0;
if ($messages) {
    $lastMessage = $messages[count($messages) - 1];
    $initialLastId = (int)$lastMessage['id'];
}
include __DIR__ . '/includes/header.php';
?>
<section class="chat-room">
    <div class="chat-room-head">
        <h1>Cộng đồng mùi hương TMH</h1>
        <p>Không gian trò chuyện dành cho thành viên đã đăng ký. Chia sẻ trải nghiệm, xin tư vấn và khám phá hương mới cùng nhau.</p>
    </div>

    <div id="communityMessages" class="chat-messages">
        <?= render_community_messages($messages, $viewerId) ?>
    </div>

    <?php if (current_user()): ?>
        <form id="chatForm" class="chat-form" method="post">
            <input type="hidden" name="action" value="send_message">
            <textarea name="message" rows="3" maxlength="1000" placeholder="Nhập tin nhắn của bạn..." required></textarea>
            <button class="btn" type="submit">Gửi tin nhắn</button>
        </form>
    <?php else: ?>
        <div class="form-card" style="max-width:100%; margin-top:12px;">
            <p>Vui lòng <a href="<?= url('login.php') ?>">đăng nhập</a> hoặc <a href="<?= url('register.php') ?>">đăng ký</a> để tham gia nhắn tin trong nhóm.</p>
        </div>
    <?php endif; ?>
</section>

<script>
(() => {
    const messagesBox = document.getElementById('communityMessages');
    const form = document.getElementById('chatForm');
    if (!messagesBox) {
        return;
    }

    const scrollToBottom = () => {
        messagesBox.scrollTop = messagesBox.scrollHeight;
    };

    let lastId = <?= $initialLastId ?>;
    const items = messagesBox.querySelectorAll('.chat-item');
    if (items.length > 0) {
        const latest = items[items.length - 1];
        const id = Number(latest.getAttribute('data-id'));
        if (!Number.isNaN(id)) {
            lastId = id;
        }
    }
    scrollToBottom();

    const reloadMessages = async () => {
        try {
            const res = await fetch('<?= BASE_URL ?>/blog.php?ajax=1', { cache: 'no-store' });
            if (!res.ok) {
                return;
            }
            const data = await res.json();
            if (typeof data.last_id !== 'number' || data.last_id === lastId) {
                return;
            }
            messagesBox.innerHTML = data.html || '';
            lastId = data.last_id;
            scrollToBottom();
        } catch (err) {
            // Keep silent to avoid interrupting chat UX.
        }
    };

    if (form) {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
            }

            try {
                const res = await fetch('<?= BASE_URL ?>/blog.php?ajax=1', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(form),
                });

                if (!res.ok) {
                    return;
                }

                const data = await res.json();
                if (!data.ok) {
                    return;
                }

                messagesBox.innerHTML = data.html || '';
                if (typeof data.last_id === 'number') {
                    lastId = data.last_id;
                }
                form.reset();
                scrollToBottom();
            } catch (err) {
                // Keep silent to avoid interrupting chat UX.
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                }
            }
        });
    }

    setInterval(reloadMessages, 4000);
})();
</script>
<?php include __DIR__ . '/includes/footer.php';

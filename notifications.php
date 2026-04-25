<?php
require_once 'includes/session_check.php';

$page_title = "Notifications";
$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];

// Mark notification as read if requested
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $notif_id = intval($_GET['mark_read']);
    $update_query = "UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(':id', $notif_id);
    $update_stmt->bindParam(':user_id', $user_id);
    $update_stmt->execute();
    redirect(SITE_URL . 'notifications.php');
}

// Get notifications grouped by time
$today_query = "SELECT * FROM notifications WHERE user_id = :user_id 
                AND DATE(created_at) = CURDATE() 
                ORDER BY created_at DESC";
$today_stmt = $db->prepare($today_query);
$today_stmt->bindParam(':user_id', $user_id);
$today_stmt->execute();
$today_notifs = $today_stmt->fetchAll();

$week_query = "SELECT * FROM notifications WHERE user_id = :user_id 
               AND DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
               AND DATE(created_at) < CURDATE()
               ORDER BY created_at DESC";
$week_stmt = $db->prepare($week_query);
$week_stmt->bindParam(':user_id', $user_id);
$week_stmt->execute();
$week_notifs = $week_stmt->fetchAll();

$older_query = "SELECT * FROM notifications WHERE user_id = :user_id 
                AND DATE(created_at) < DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                ORDER BY created_at DESC LIMIT 10";
$older_stmt = $db->prepare($older_query);
$older_stmt->bindParam(':user_id', $user_id);
$older_stmt->execute();
$older_notifs = $older_stmt->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!-- Main Content -->
<main>
  <header>
    <h3>Notifications</h3>
    <nav>
      <a href="index.php">Home</a>
      <a href="menu.php">Menu</a>
      <a href="orders.php">My Orders</a>
      <a href="profile.php">Profile</a>
    </nav>
  </header>

  <div class="notif-container" style="padding: 30px; overflow-y: auto;">
    <h2 style="margin-bottom: 20px; font-weight: 600;">Recent Notifications</h2>

    <?php if (count($today_notifs) == 0 && count($week_notifs) == 0 && count($older_notifs) == 0): ?>
      <div style="text-align: center; padding: 60px 20px;">
        <p style="font-size: 1.2rem; color: #7D6E6E; margin-bottom: 20px;">No notifications yet</p>
        <a href="menu.php" style="display: inline-block; padding: 12px 30px; background: #B76E09; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600;">
          Browse Menu
        </a>
      </div>
    <?php else: ?>
      
      <?php if (count($today_notifs) > 0): ?>
        <!-- Group 1: Today -->
        <div class="notif-group" style="margin-bottom: 30px;">
          <h4 style="color: #7D6E6E; font-weight: 600; margin-bottom: 12px;">Today</h4>
          <?php foreach ($today_notifs as $notif): ?>
            <div class="notif-card <?php echo !$notif['is_read'] ? 'unread' : ''; ?>" 
                 data-notif-id="<?php echo $notif['id']; ?>" 
                 data-is-read="<?php echo $notif['is_read']; ?>"
                 onclick="markAsRead(<?php echo $notif['id']; ?>, <?php echo $notif['is_read']; ?>)"
                 style="background: <?php echo $notif['is_read'] ? '#fff' : '#fffaf3'; ?>; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); padding: 15px 20px; display: flex; align-items: center; gap: 15px; margin-bottom: 10px; transition: all 0.3s ease;">
              <div class="notif-icon" style="width: 45px; height: 45px; border-radius: 50%; background-color: #F9E4C1; color: #B76E09; display: flex; justify-content: center; align-items: center; font-size: 1.3rem; font-weight: 700;">
                📬
              </div>
              <div class="notif-details" style="flex: 1;">
                <h5 style="margin: 0; font-size: 1rem; font-weight: 600; color: #000;">
                  <?php echo htmlspecialchars($notif['title']); ?>
                  <?php if (!$notif['is_read']): ?>
                    <span class="new-badge" style="display: inline-block; background: #dc3545; color: #fff; font-size: 0.65rem; padding: 2px 6px; border-radius: 8px; margin-left: 8px; font-weight: 700;">NEW</span>
                  <?php endif; ?>
                </h5>
                <p style="margin: 4px 0 0; color: #7D6E6E; font-size: 0.9rem;">
                  <?php echo htmlspecialchars($notif['message']); ?>
                </p>
              </div>
              <div class="notif-time" style="font-size: 0.85rem; color: #B76E09; font-weight: 600;">
                <?php 
                $time_diff = time() - strtotime($notif['created_at']);
                if ($time_diff < 60) {
                    echo 'Just now';
                } elseif ($time_diff < 3600) {
                    echo floor($time_diff / 60) . ' mins ago';
                } else {
                    echo floor($time_diff / 3600) . ' hours ago';
                }
                ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if (count($week_notifs) > 0): ?>
        <!-- Group 2: This Week -->
        <div class="notif-group" style="margin-bottom: 30px;">
          <h4 style="color: #7D6E6E; font-weight: 600; margin-bottom: 12px;">This Week</h4>
          <?php foreach ($week_notifs as $notif): ?>
            <div class="notif-card <?php echo !$notif['is_read'] ? 'unread' : ''; ?>" 
                 data-notif-id="<?php echo $notif['id']; ?>" 
                 data-is-read="<?php echo $notif['is_read']; ?>"
                 onclick="markAsRead(<?php echo $notif['id']; ?>, <?php echo $notif['is_read']; ?>)"
                 style="background: <?php echo $notif['is_read'] ? '#fff' : '#fffaf3'; ?>; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); padding: 15px 20px; display: flex; align-items: center; gap: 15px; margin-bottom: 10px; transition: all 0.3s ease;">
              <div class="notif-icon" style="width: 45px; height: 45px; border-radius: 50%; background-color: #F9E4C1; color: #B76E09; display: flex; justify-content: center; align-items: center; font-size: 1.3rem; font-weight: 700;">
                📦
              </div>
              <div class="notif-details" style="flex: 1;">
                <h5 style="margin: 0; font-size: 1rem; font-weight: 600; color: #000;">
                  <?php echo htmlspecialchars($notif['title']); ?>
                  <?php if (!$notif['is_read']): ?>
                    <span class="new-badge" style="display: inline-block; background: #dc3545; color: #fff; font-size: 0.65rem; padding: 2px 6px; border-radius: 8px; margin-left: 8px; font-weight: 700;">NEW</span>
                  <?php endif; ?>
                </h5>
                <p style="margin: 4px 0 0; color: #7D6E6E; font-size: 0.9rem;">
                  <?php echo htmlspecialchars($notif['message']); ?>
                </p>
              </div>
              <div class="notif-time" style="font-size: 0.85rem; color: #B76E09; font-weight: 600;">
                <?php echo date('M d', strtotime($notif['created_at'])); ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if (count($older_notifs) > 0): ?>
        <!-- Group 3: Earlier -->
        <div class="notif-group" style="margin-bottom: 30px;">
          <h4 style="color: #7D6E6E; font-weight: 600; margin-bottom: 12px;">Earlier</h4>
          <?php foreach ($older_notifs as $notif): ?>
            <div class="notif-card <?php echo !$notif['is_read'] ? 'unread' : ''; ?>" 
                 data-notif-id="<?php echo $notif['id']; ?>" 
                 data-is-read="<?php echo $notif['is_read']; ?>"
                 onclick="markAsRead(<?php echo $notif['id']; ?>, <?php echo $notif['is_read']; ?>)"
                 style="background: <?php echo $notif['is_read'] ? '#fff' : '#fffaf3'; ?>; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); padding: 15px 20px; display: flex; align-items: center; gap: 15px; margin-bottom: 10px; transition: all 0.3s ease;">
              <div class="notif-icon" style="width: 45px; height: 45px; border-radius: 50%; background-color: #F9E4C1; color: #B76E09; display: flex; justify-content: center; align-items: center; font-size: 1.3rem; font-weight: 700;">
                🔔
              </div>
              <div class="notif-details" style="flex: 1;">
                <h5 style="margin: 0; font-size: 1rem; font-weight: 600; color: #000;">
                  <?php echo htmlspecialchars($notif['title']); ?>
                  <?php if (!$notif['is_read']): ?>
                    <span class="new-badge" style="display: inline-block; background: #dc3545; color: #fff; font-size: 0.65rem; padding: 2px 6px; border-radius: 8px; margin-left: 8px; font-weight: 700;">NEW</span>
                  <?php endif; ?>
                </h5>
                <p style="margin: 4px 0 0; color: #7D6E6E; font-size: 0.9rem;">
                  <?php echo htmlspecialchars($notif['message']); ?>
                </p>
              </div>
              <div class="notif-time" style="font-size: 0.85rem; color: #B76E09; font-weight: 600;">
                <?php echo date('M d, Y', strtotime($notif['created_at'])); ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      
    <?php endif; ?>
  </div>

<?php 
$additional_js = "
<script>
function markAsRead(notifId, isRead) {
    // Only mark as read if it's currently unread
    if (isRead == 1) {
        return; // Already read, do nothing
    }
    
    // Send AJAX request to mark as read
    fetch('ajax/mark_notification_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'notification_id=' + notifId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the card visually
            const card = document.querySelector('.notif-card[data-notif-id=\"' + notifId + '\"]');
            if (card) {
                card.style.background = '#fff';
                card.classList.remove('unread');
                card.setAttribute('data-is-read', '1');
                
                // Remove NEW badge
                const badge = card.querySelector('.new-badge');
                if (badge) {
                    badge.remove();
                }
            }
            
            // Update sidebar badge count
            updateBadgeCount();
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

function updateBadgeCount() {
    // Count remaining unread notifications
    const unreadCards = document.querySelectorAll('.notif-card.unread');
    const unreadCount = unreadCards.length;
    
    // Update the sidebar badge
    const badge = document.querySelector('.notif-badge');
    if (badge) {
        if (unreadCount > 0) {
            badge.textContent = unreadCount;
        } else {
            badge.remove(); // Remove badge when no unread notifications
        }
    }
}
</script>
";

include 'includes/footer.php'; 
?>

<style>
.notif-card:hover {
  background-color: #fffaf3 !important;
  transform: translateY(-2px);
  box-shadow: 0 2px 8px rgba(0,0,0,0.12) !important;
  cursor: pointer;
}

.notif-card.unread {
  border-left: 4px solid #B76E09;
}
</style>

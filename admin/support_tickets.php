<?php
require_once '../includes/admin_check.php';

$page_title = "Support Tickets";
$database = new Database();
$db = $database->getConnection();

// Handle admin reply
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_reply'])) {
    $ticket_id = intval($_POST['ticket_id']);
    $message = clean_input($_POST['message']);
    $new_status = clean_input($_POST['status']);
    
    if (!empty($message)) {
        $msg_query = "INSERT INTO support_messages (ticket_id, sender_id, is_admin, message) 
                     VALUES (:ticket_id, :sender_id, 1, :message)";
        $msg_stmt = $db->prepare($msg_query);
        $msg_stmt->bindParam(':ticket_id', $ticket_id);
        $msg_stmt->bindParam(':sender_id', $_SESSION['user_id']);
        $msg_stmt->bindParam(':message', $message);
        $msg_stmt->execute();
        
        // Update ticket status
        $update_query = "UPDATE support_tickets SET status = :status, updated_at = NOW() WHERE id = :id";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->bindParam(':status', $new_status);
        $update_stmt->bindParam(':id', $ticket_id);
        $update_stmt->execute();
        
        header('Location: support_tickets.php?ticket=' . $ticket_id . '&replied=1');
        exit();
    }
}

// Get all tickets
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$tickets_query = "SELECT st.*, u.first_name, u.last_name, u.email 
                  FROM support_tickets st 
                  JOIN users u ON st.user_id = u.id";

if ($status_filter != 'all') {
    $tickets_query .= " WHERE st.status = :status";
}

$tickets_query .= " ORDER BY st.updated_at DESC";

$tickets_stmt = $db->prepare($tickets_query);
if ($status_filter != 'all') {
    $tickets_stmt->bindParam(':status', $status_filter);
}
$tickets_stmt->execute();
$tickets = $tickets_stmt->fetchAll();

// Get specific ticket if selected
$selected_ticket = null;
$messages = [];
if (isset($_GET['ticket'])) {
    $ticket_id = intval($_GET['ticket']);
    $ticket_query = "SELECT st.*, u.first_name, u.last_name, u.email 
                     FROM support_tickets st 
                     JOIN users u ON st.user_id = u.id 
                     WHERE st.id = :id";
    $ticket_stmt = $db->prepare($ticket_query);
    $ticket_stmt->bindParam(':id', $ticket_id);
    $ticket_stmt->execute();
    $selected_ticket = $ticket_stmt->fetch();
    
    if ($selected_ticket) {
        $msg_query = "SELECT sm.*, u.first_name, u.last_name 
                      FROM support_messages sm 
                      JOIN users u ON sm.sender_id = u.id 
                      WHERE sm.ticket_id = :ticket_id 
                      ORDER BY sm.created_at ASC";
        $msg_stmt = $db->prepare($msg_query);
        $msg_stmt->bindParam(':ticket_id', $ticket_id);
        $msg_stmt->execute();
        $messages = $msg_stmt->fetchAll();
    }
}

// Get ticket statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Open' THEN 1 ELSE 0 END) as open,
    SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) as resolved,
    SUM(CASE WHEN status = 'Closed' THEN 1 ELSE 0 END) as closed
    FROM support_tickets";
$stats = $db->query($stats_query)->fetch();

include '../includes/header.php';
?>

<!-- Sidebar -->
<aside>
  <div class="logo">🍴 DineClick Admin</div>
  <div class="sidebar-menu">
    <a href="dashboard.php">Dashboard</a>
    <a href="products.php">Products</a>
    <a href="orders_manage.php">Manage Orders</a>
    <a href="categories.php">Categories</a>
    <a href="support_tickets.php" class="active">Support Tickets</a>
    <a href="reports.php">Sales Reports</a>
    <a href="../logout.php">Logout</a>
  </div>
</aside>

<!-- Main Content -->
<main>
  <header>
    <h3>Support Tickets</h3>
    <nav>
      <a href="dashboard.php">Dashboard</a>
      <a href="products.php">Products</a>
      <a href="orders_manage.php">Orders</a>
      <a href="support_tickets.php" class="active">Support</a>
    </nav>
  </header>

  <div style="padding: 30px; overflow-y: auto;">
    <?php if (isset($_GET['replied'])): ?>
      <div class="alert alert-success">Reply sent successfully!</div>
    <?php endif; ?>

    <?php if (!$selected_ticket): ?>
      <!-- Ticket List View -->
      <!-- Stats Cards -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 25px;">
        <div style="background: linear-gradient(135deg, #28a745 0%, #20873a 100%); padding: 20px; border-radius: 10px; color: #fff;">
          <h4 style="margin: 0; font-size: 0.85rem; opacity: 0.9;">Open Tickets</h4>
          <p style="margin: 8px 0 0 0; font-size: 1.8rem; font-weight: 700;"><?php echo $stats['open']; ?></p>
        </div>
        
        <div style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); padding: 20px; border-radius: 10px; color: #fff;">
          <h4 style="margin: 0; font-size: 0.85rem; opacity: 0.9;">In Progress</h4>
          <p style="margin: 8px 0 0 0; font-size: 1.8rem; font-weight: 700;"><?php echo $stats['in_progress']; ?></p>
        </div>
        
        <div style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); padding: 20px; border-radius: 10px; color: #fff;">
          <h4 style="margin: 0; font-size: 0.85rem; opacity: 0.9;">Resolved</h4>
          <p style="margin: 8px 0 0 0; font-size: 1.8rem; font-weight: 700;"><?php echo $stats['resolved']; ?></p>
        </div>
        
        <div style="background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); padding: 20px; border-radius: 10px; color: #fff;">
          <h4 style="margin: 0; font-size: 0.85rem; opacity: 0.9;">Closed</h4>
          <p style="margin: 8px 0 0 0; font-size: 1.8rem; font-weight: 700;"><?php echo $stats['closed']; ?></p>
        </div>
      </div>

      <!-- Filter -->
      <div style="margin-bottom: 20px;">
        <select onchange="location.href='?status=' + this.value" 
                style="padding: 10px 15px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.95rem;">
          <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Tickets</option>
          <option value="Open" <?php echo $status_filter == 'Open' ? 'selected' : ''; ?>>Open</option>
          <option value="In Progress" <?php echo $status_filter == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
          <option value="Resolved" <?php echo $status_filter == 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
          <option value="Closed" <?php echo $status_filter == 'Closed' ? 'selected' : ''; ?>>Closed</option>
        </select>
      </div>

      <!-- Tickets List -->
      <div style="background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.08);">
        <h3 style="margin: 0 0 20px 0;">Support Tickets</h3>
        
        <?php if (count($tickets) > 0): ?>
          <table style="width: 100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #f8f9fa;">
                <th style="padding: 12px; text-align: left; font-weight: 600;">Ticket #</th>
                <th style="padding: 12px; text-align: left; font-weight: 600;">Subject</th>
                <th style="padding: 12px; text-align: left; font-weight: 600;">Customer</th>
                <th style="padding: 12px; text-align: left; font-weight: 600;">Category</th>
                <th style="padding: 12px; text-align: left; font-weight: 600;">Status</th>
                <th style="padding: 12px; text-align: left; font-weight: 600;">Updated</th>
                <th style="padding: 12px; text-align: left; font-weight: 600;">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($tickets as $ticket): ?>
                <?php
                $status_colors = [
                    'Open' => '#28a745',
                    'In Progress' => '#ffc107',
                    'Resolved' => '#17a2b8',
                    'Closed' => '#6c757d'
                ];
                $status_color = $status_colors[$ticket['status']] ?? '#6c757d';
                ?>
                <tr style="border-bottom: 1px solid #f0f0f0;">
                  <td style="padding: 12px;">#<?php echo $ticket['id']; ?></td>
                  <td style="padding: 12px; font-weight: 600;">
                    <?php echo htmlspecialchars($ticket['subject']); ?>
                  </td>
                  <td style="padding: 12px;">
                    <?php echo htmlspecialchars($ticket['first_name'] . ' ' . $ticket['last_name']); ?>
                  </td>
                  <td style="padding: 12px; color: #7D6E6E;">
                    <?php echo htmlspecialchars($ticket['category']); ?>
                  </td>
                  <td style="padding: 12px;">
                    <span style="padding: 5px 10px; background: <?php echo $status_color; ?>; color: #fff; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                      <?php echo $ticket['status']; ?>
                    </span>
                  </td>
                  <td style="padding: 12px; color: #7D6E6E; font-size: 0.9rem;">
                    <?php echo date('M d, Y', strtotime($ticket['updated_at'])); ?>
                  </td>
                  <td style="padding: 12px;">
                    <a href="?ticket=<?php echo $ticket['id']; ?>" 
                       style="color: #B76E09; text-decoration: none; font-weight: 600;">
                      View →
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p style="text-align: center; color: #7D6E6E; padding: 40px;">No tickets found.</p>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <!-- Ticket Detail View -->
      <div style="max-width: 1000px; margin: 0 auto;">
        <a href="support_tickets.php" style="display: inline-flex; align-items: center; gap: 8px; color: #B76E09; text-decoration: none; margin-bottom: 20px; font-weight: 600;">
          ← Back to All Tickets
        </a>
        
        <div style="background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); margin-bottom: 20px;">
          <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
            <div>
              <h2 style="margin: 0 0 10px 0; font-weight: 600;"><?php echo htmlspecialchars($selected_ticket['subject']); ?></h2>
              <div style="display: flex; gap: 15px; color: #7D6E6E; font-size: 0.9rem;">
                <span>👤 <?php echo htmlspecialchars($selected_ticket['first_name'] . ' ' . $selected_ticket['last_name']); ?></span>
                <span>📧 <?php echo htmlspecialchars($selected_ticket['email']); ?></span>
                <span>📁 <?php echo htmlspecialchars($selected_ticket['category']); ?></span>
                <span>Ticket #<?php echo $selected_ticket['id']; ?></span>
              </div>
            </div>
            <?php
            $status_colors = [
                'Open' => '#28a745',
                'In Progress' => '#ffc107',
                'Resolved' => '#17a2b8',
                'Closed' => '#6c757d'
            ];
            $status_color = $status_colors[$selected_ticket['status']] ?? '#6c757d';
            ?>
            <span style="padding: 6px 14px; background: <?php echo $status_color; ?>; color: #fff; border-radius: 15px; font-size: 0.9rem; font-weight: 600;">
              <?php echo $selected_ticket['status']; ?>
            </span>
          </div>
        </div>

        <!-- Messages Thread -->
        <div style="background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); margin-bottom: 20px;">
          <h3 style="margin: 0 0 20px 0; font-size: 1.1rem; color: #000;">Conversation</h3>
          
          <div style="display: flex; flex-direction: column; gap: 20px;">
            <?php foreach ($messages as $msg): ?>
              <div style="display: flex; gap: 15px; <?php echo $msg['is_admin'] ? 'flex-direction: row-reverse;' : 'flex-direction: row;'; ?>">
                <div style="flex-shrink: 0;">
                  <div style="width: 45px; height: 45px; border-radius: 50%; background: <?php echo $msg['is_admin'] ? '#B76E09' : '#4CAF50'; ?>; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem;">
                    <?php echo $msg['is_admin'] ? '🛠' : strtoupper(substr($msg['first_name'], 0, 1)); ?>
                  </div>
                </div>
                <div style="flex: 1; max-width: 70%;">
                  <div style="background: <?php echo $msg['is_admin'] ? '#e3f2fd' : '#f8f9fa'; ?>; padding: 15px; border-radius: 12px;">
                    <div style="font-weight: 600; margin-bottom: 5px; color: #000;">
                      <?php echo $msg['is_admin'] ? 'Support Team (You)' : htmlspecialchars($msg['first_name'] . ' ' . $msg['last_name']); ?>
                    </div>
                    <div style="color: #000; line-height: 1.6; white-space: pre-wrap;">
                      <?php echo htmlspecialchars($msg['message']); ?>
                    </div>
                    <div style="font-size: 0.8rem; color: #7D6E6E; margin-top: 8px;">
                      <?php echo date('M d, Y h:i A', strtotime($msg['created_at'])); ?>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Admin Reply Form -->
        <div style="background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.08);">
          <h3 style="margin: 0 0 15px 0; font-size: 1.1rem; color: #000;">Send Reply</h3>
          <form method="POST">
            <input type="hidden" name="ticket_id" value="<?php echo $selected_ticket['id']; ?>">
            
            <div style="margin-bottom: 15px;">
              <label style="display: block; margin-bottom: 5px; font-weight: 600;">Update Status</label>
              <select name="status" required
                      style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.95rem;">
                <option value="Open" <?php echo $selected_ticket['status'] == 'Open' ? 'selected' : ''; ?>>Open</option>
                <option value="In Progress" <?php echo $selected_ticket['status'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                <option value="Resolved" <?php echo $selected_ticket['status'] == 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                <option value="Closed" <?php echo $selected_ticket['status'] == 'Closed' ? 'selected' : ''; ?>>Closed</option>
              </select>
            </div>
            
            <div style="margin-bottom: 15px;">
              <label style="display: block; margin-bottom: 5px; font-weight: 600;">Your Reply</label>
              <textarea name="message" required rows="5" placeholder="Type your reply here..."
                        style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.95rem; resize: vertical;"></textarea>
            </div>
            
            <button type="submit" name="admin_reply"
                    style="padding: 12px 30px; background: #B76E09; color: #fff; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 1rem;">
              Send Reply & Update Status
            </button>
          </form>
        </div>
      </div>
    <?php endif; ?>
  </div>

<?php include '../includes/footer.php'; ?>

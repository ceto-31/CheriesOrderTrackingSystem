<?php
require_once 'includes/session_check.php';

$page_title = "Contact Support";
$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Handle new ticket submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_ticket'])) {
    $subject = clean_input($_POST['subject']);
    $category = clean_input($_POST['category']);
    $message = clean_input($_POST['message']);
    
    if (!empty($subject) && !empty($category) && !empty($message)) {
        // Create ticket
        $ticket_query = "INSERT INTO support_tickets (user_id, subject, category, status, priority) 
                         VALUES (:user_id, :subject, :category, 'Open', 'Medium')";
        $ticket_stmt = $db->prepare($ticket_query);
        $ticket_stmt->bindParam(':user_id', $user_id);
        $ticket_stmt->bindParam(':subject', $subject);
        $ticket_stmt->bindParam(':category', $category);
        
        if ($ticket_stmt->execute()) {
            $ticket_id = $db->lastInsertId();
            
            // Add first message
            $msg_query = "INSERT INTO support_messages (ticket_id, sender_id, is_admin, message) 
                         VALUES (:ticket_id, :sender_id, 0, :message)";
            $msg_stmt = $db->prepare($msg_query);
            $msg_stmt->bindParam(':ticket_id', $ticket_id);
            $msg_stmt->bindParam(':sender_id', $user_id);
            $msg_stmt->bindParam(':message', $message);
            $msg_stmt->execute();
            
            header('Location: support.php?ticket=' . $ticket_id . '&success=1');
            exit();
        }
    }
}

// Handle reply to existing ticket
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reply_ticket'])) {
    $ticket_id = intval($_POST['ticket_id']);
    $message = clean_input($_POST['message']);
    
    if (!empty($message)) {
        // Verify ticket belongs to user
        $verify_query = "SELECT id FROM support_tickets WHERE id = :id AND user_id = :user_id";
        $verify_stmt = $db->prepare($verify_query);
        $verify_stmt->bindParam(':id', $ticket_id);
        $verify_stmt->bindParam(':user_id', $user_id);
        $verify_stmt->execute();
        
        if ($verify_stmt->rowCount() > 0) {
            $msg_query = "INSERT INTO support_messages (ticket_id, sender_id, is_admin, message) 
                         VALUES (:ticket_id, :sender_id, 0, :message)";
            $msg_stmt = $db->prepare($msg_query);
            $msg_stmt->bindParam(':ticket_id', $ticket_id);
            $msg_stmt->bindParam(':sender_id', $user_id);
            $msg_stmt->bindParam(':message', $message);
            $msg_stmt->execute();
            
            // Update ticket status if closed
            $update_query = "UPDATE support_tickets SET status = 'Open', updated_at = NOW() WHERE id = :id";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->bindParam(':id', $ticket_id);
            $update_stmt->execute();
            
            header('Location: support.php?ticket=' . $ticket_id . '&replied=1');
            exit();
        }
    }
}

// Get user's tickets
$tickets_query = "SELECT * FROM support_tickets WHERE user_id = :user_id ORDER BY updated_at DESC";
$tickets_stmt = $db->prepare($tickets_query);
$tickets_stmt->bindParam(':user_id', $user_id);
$tickets_stmt->execute();
$tickets = $tickets_stmt->fetchAll();

// Get specific ticket if selected
$selected_ticket = null;
$messages = [];
if (isset($_GET['ticket'])) {
    $ticket_id = intval($_GET['ticket']);
    $ticket_query = "SELECT * FROM support_tickets WHERE id = :id AND user_id = :user_id";
    $ticket_stmt = $db->prepare($ticket_query);
    $ticket_stmt->bindParam(':id', $ticket_id);
    $ticket_stmt->bindParam(':user_id', $user_id);
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

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!-- Main Content -->
<main>
  <header>
    <h3>Contact Support</h3>
    <nav>
      <a href="index.php">Home</a>
      <a href="help.php">Help & FAQ</a>
      <a href="support.php" class="active">Support</a>
    </nav>
  </header>

  <div style="padding: 30px; overflow-y: auto;">
    <?php if (isset($_GET['success'])): ?>
      <div class="alert alert-success">Ticket created successfully! Our team will respond soon.</div>
    <?php endif; ?>
    
    <?php if (isset($_GET['replied'])): ?>
      <div class="alert alert-success">Reply sent successfully!</div>
    <?php endif; ?>

    <?php if (!$selected_ticket): ?>
      <!-- Ticket List View -->
      <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 25px;">
        <div>
          <h2 style="margin-bottom: 20px; font-weight: 600;">Your Support Tickets</h2>
          
          <?php if (count($tickets) > 0): ?>
            <div style="display: flex; flex-direction: column; gap: 15px;">
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
                <a href="?ticket=<?php echo $ticket['id']; ?>" style="text-decoration: none;">
                  <div style="background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); border-left: 4px solid <?php echo $status_color; ?>; transition: all 0.3s;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                      <h4 style="margin: 0; color: #000; font-size: 1.1rem;">
                        <?php echo htmlspecialchars($ticket['subject']); ?>
                      </h4>
                      <span style="padding: 4px 10px; background: <?php echo $status_color; ?>; color: #fff; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                        <?php echo $ticket['status']; ?>
                      </span>
                    </div>
                    <div style="display: flex; gap: 20px; color: #7D6E6E; font-size: 0.9rem;">
                      <span>📁 <?php echo htmlspecialchars($ticket['category']); ?></span>
                      <span>🕐 <?php echo date('M d, Y h:i A', strtotime($ticket['created_at'])); ?></span>
                    </div>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div style="text-align: center; padding: 40px; background: #fff; border-radius: 10px;">
              <p style="color: #7D6E6E; margin-bottom: 20px;">You haven't created any support tickets yet.</p>
              <p style="color: #7D6E6E;">Create a new ticket using the form on the right.</p>
            </div>
          <?php endif; ?>
        </div>

        <!-- Create New Ticket Form -->
        <div>
          <div style="background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); position: sticky; top: 90px;">
            <h3 style="margin: 0 0 20px 0; font-size: 1.2rem; color: #000;">Create New Ticket</h3>
            
            <form method="POST">
              <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #000;">Subject</label>
                <input type="text" name="subject" required
                       style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.95rem;">
              </div>
              
              <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #000;">Category</label>
                <select name="category" required
                        style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.95rem;">
                  <option value="">Select Category</option>
                  <option value="Orders">Orders & Delivery</option>
                  <option value="Payment">Payment & Billing</option>
                  <option value="Account">Account Issues</option>
                  <option value="Technical">Technical Problem</option>
                  <option value="General">General Inquiry</option>
                </select>
              </div>
              
              <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #000;">Message</label>
                <textarea name="message" required rows="5"
                          style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.95rem; resize: vertical;"></textarea>
              </div>
              
              <button type="submit" name="create_ticket"
                      style="width: 100%; padding: 12px; background: #B76E09; color: #fff; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 1rem;">
                Submit Ticket
              </button>
            </form>
          </div>
        </div>
      </div>
    <?php else: ?>
      <!-- Ticket Detail View -->
      <div style="max-width: 900px; margin: 0 auto;">
        <a href="support.php" style="display: inline-flex; align-items: center; gap: 8px; color: #B76E09; text-decoration: none; margin-bottom: 20px; font-weight: 600;">
          ← Back to Tickets
        </a>
        
        <div style="background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); margin-bottom: 20px;">
          <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
            <div>
              <h2 style="margin: 0 0 10px 0; font-weight: 600;"><?php echo htmlspecialchars($selected_ticket['subject']); ?></h2>
              <div style="display: flex; gap: 15px; color: #7D6E6E; font-size: 0.9rem;">
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
              <div style="display: flex; gap: 15px; <?php echo $msg['is_admin'] ? 'flex-direction: row;' : 'flex-direction: row-reverse;'; ?>">
                <div style="flex-shrink: 0;">
                  <div style="width: 45px; height: 45px; border-radius: 50%; background: <?php echo $msg['is_admin'] ? '#B76E09' : '#4CAF50'; ?>; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem;">
                    <?php echo $msg['is_admin'] ? '🛠' : strtoupper(substr($msg['first_name'], 0, 1)); ?>
                  </div>
                </div>
                <div style="flex: 1; max-width: 70%;">
                  <div style="background: <?php echo $msg['is_admin'] ? '#f8f9fa' : '#e3f2fd'; ?>; padding: 15px; border-radius: 12px;">
                    <div style="font-weight: 600; margin-bottom: 5px; color: #000;">
                      <?php echo $msg['is_admin'] ? 'Support Team' : htmlspecialchars($msg['first_name'] . ' ' . $msg['last_name']); ?>
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

        <!-- Reply Form -->
        <?php if ($selected_ticket['status'] != 'Closed'): ?>
          <div style="background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.08);">
            <h3 style="margin: 0 0 15px 0; font-size: 1.1rem; color: #000;">Send Reply</h3>
            <form method="POST">
              <input type="hidden" name="ticket_id" value="<?php echo $selected_ticket['id']; ?>">
              <textarea name="message" required rows="4" placeholder="Type your message here..."
                        style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.95rem; resize: vertical; margin-bottom: 15px;"></textarea>
              <button type="submit" name="reply_ticket"
                      style="padding: 10px 25px; background: #B76E09; color: #fff; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.95rem;">
                Send Reply
              </button>
            </form>
          </div>
        <?php else: ?>
          <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 10px;">
            <p style="color: #7D6E6E; margin: 0;">This ticket is closed. Please create a new ticket if you need further assistance.</p>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

<?php include 'includes/footer.php'; ?>

<style>
a[href^="?ticket"] > div:hover {
  transform: translateX(5px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}
</style>

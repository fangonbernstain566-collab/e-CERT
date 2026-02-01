<?php
session_start();
include 'config.php'; // Pulls the current $event_name

// 1. SECURITY CHECK
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require 'db.php';

$participants = [];
$error_msg = "";
$success_msg = "";

// --- HANDLE EVENT NAME UPDATE ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_event_name'])) {
    $new_name = htmlspecialchars($_POST['new_event_name']);
    if (file_put_contents("event_name.txt", $new_name)) {
        $success_msg = "✅ Event title updated to: " . $new_name;
        $event_name = $new_name; 
    } else {
        $error_msg = "❌ Failed to save event name. Check file permissions.";
    }
}

// --- HANDLE TEMPLATE UPLOAD ---
// Note: This block still handles the upload when the AJAX call hits this page
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['new_template'])) {
    $target_dir = "assets/";
    $target_file = $target_dir . "CIT(1).png";
    $imageFileType = strtolower(pathinfo($_FILES["new_template"]["name"], PATHINFO_EXTENSION));

    if($imageFileType != "png") {
        echo "error:Only PNG files are allowed.";
        exit();
    } else {
        if (move_uploaded_file($_FILES["new_template"]["tmp_name"], $target_file)) {
            echo "success";
            exit();
        } else {
            echo "error:Upload failed on server.";
            exit();
        }
    }
}

// 2. HANDLE STATUS NOTIFICATIONS
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'deleted') {
        $success_msg = "✅ Record successfully removed from the database.";
    } elseif ($_GET['status'] === 'resent') {
        $success_msg = "📧 Certificate has been resent to the participant.";
    } elseif ($_GET['status'] === 'uploaded') {
        $success_msg = "✅ Template updated successfully!";
    } elseif ($_GET['status'] === 'error') {
        $error_msg = "❌ Error: " . htmlspecialchars($_GET['message']);
    }
}

// 3. FETCH DATA
try {
    $stmt = $pdo->query("SELECT * FROM participants ORDER BY created_at DESC");
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_msg = "Database Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIT Admin Console</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #003366; --danger: #ef4444; --success: #22c55e; --blue: #2563eb; --slate: #64748b; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1e293b; margin: 0; }
        
        .nav-bar { background: #ffffff; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 100; }
        .nav-bar h2 { margin: 0; font-size: 20px; color: var(--primary); }
        .nav-actions a { text-decoration: none; padding: 10px 18px; border-radius: 8px; font-size: 14px; font-weight: 600; transition: 0.2s; }
        
        .btn-export { background: var(--primary); color: white !important; margin-right: 10px; }
        .btn-reg { color: var(--slate); margin-right: 10px; }
        .btn-logout { background: #fee2e2; color: var(--danger); }

        .content { max-width: 1200px; margin: 40px auto; padding: 0 20px; }

        .alert { padding: 15px 25px; border-radius: 10px; margin-bottom: 25px; font-size: 14px; font-weight: 500; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; display: flex; flex-direction: column; }
        .stat-card h3 { margin: 0 0 15px 0; font-size: 12px; color: var(--slate); text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-count { margin: 0; font-size: 32px; font-weight: 700; color: #0f172a; }

        /* Professional Upload UI */
        .upload-area { border: 2px dashed #e2e8f0; padding: 20px; border-radius: 10px; background: #f8fafc; text-align: center; cursor: pointer; transition: 0.3s; position: relative; }
        .upload-area:hover { border-color: var(--blue); background: #f0f7ff; }
        .upload-area p { margin: 0; font-size: 12px; color: var(--slate); }
        .upload-area strong { color: var(--blue); display: block; margin-bottom: 4px; }
        .file-input-hidden { position: absolute; opacity: 0; width: 100%; height: 100%; top: 0; left: 0; cursor: pointer; }
        
        .progress-container { display: none; width: 100%; background-color: #e2e8f0; border-radius: 10px; margin-top: 15px; overflow: hidden; }
        .progress-bar { width: 0%; height: 8px; background: var(--blue); transition: width 0.1s linear; }
        .processing-text { display: none; font-size: 11px; color: var(--blue); font-weight: 600; margin-top: 5px; text-align: center; text-transform: uppercase; }

        .btn-submit-pro { background: var(--primary); color: white; border: none; padding: 12px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 13px; margin-top: 10px; transition: 0.2s; }
        .btn-submit-pro:hover:not(:disabled) { opacity: 0.9; transform: translateY(-1px); }
        .btn-submit-pro:disabled { background: var(--slate); cursor: not-allowed; }

        .event-input { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; box-sizing: border-box; margin-bottom: 10px; }

        .table-card { background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: #f1f5f9; padding: 16px; font-size: 12px; font-weight: 600; color: #475569; text-transform: uppercase; }
        td { padding: 16px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        .badge { padding: 4px 12px; border-radius: 9999px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .badge-sent { background: #dcfce7; color: #166534; }
        .btn-action { text-decoration: none; font-size: 12px; font-weight: 700; padding: 6px 12px; border-radius: 6px; border: 1px solid transparent; transition: 0.2s; margin-right: 5px;}
        .btn-resend { color: var(--blue); border-color: #bfdbfe; }
        .btn-delete { color: var(--danger); border-color: #fecaca; }
        .search-box { width: 100%; padding: 15px; margin-bottom: 25px; border-radius: 10px; border: 1px solid #e2e8f0; font-size: 15px; box-sizing: border-box; }
    </style>
</head>
<body>

<div class="nav-bar">
    <h2>CIT Admin Console</h2>
    <div class="nav-actions">
        <a href="export.php" class="btn-export">📥 Export to CSV</a>
        <a href="index.php" class="btn-reg">Registration View</a>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>
</div>

<div class="content">
    <?php if ($success_msg): ?>
        <div class="alert alert-success"><?= $success_msg ?></div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="alert alert-error" id="phpErrorDisplay"><?= $error_msg ?></div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Participants</h3>
            <p class="stat-count"><?= count($participants) ?></p>
        </div>

        <div class="stat-card" style="border-top: 4px solid var(--success);">
            <h3>Certificates Sent</h3>
            <p class="stat-count"><?php 
                $count = 0;
                foreach($participants as $p) { if($p['status'] == 'Sent') $count++; }
                echo $count;
            ?></p>
        </div>

        <div class="stat-card" style="border-top: 4px solid var(--danger);">
            <h3>Event Name Settings</h3>
            <form action="admin.php" method="POST" style="display:flex; flex-direction:column; flex:1; justify-content:space-between;">
                <input type="text" name="new_event_name" class="event-input" value="<?= htmlspecialchars($event_name) ?>" required>
                <button type="submit" class="btn-submit-pro" style="background: var(--danger);">Update Event Title</button>
            </form>
        </div>

        <div class="stat-card" style="border-top: 4px solid var(--blue);">
            <h3>Update Live Preview</h3>
            <div style="display:flex; flex-direction:column; flex:1; justify-content:space-between;">
                <div class="upload-area">
                    <input type="file" id="fileInput" accept="image/png" class="file-input-hidden" onchange="updateFileName()">
                    <strong>Click to browse</strong>
                    <p id="fileNameDisplay">PNG files only (Max 5MB)</p>
                </div>

                <div class="progress-container" id="progContainer">
                    <div class="progress-bar" id="progBar"></div>
                </div>
                <div class="processing-text" id="procText">0% Uploaded</div>

                <button type="button" class="btn-submit-pro" id="uploadBtn" onclick="handleRealUpload()">Upload & Refresh Template</button>
            </div>
        </div>
    </div>

    <input type="text" id="searchInput" class="search-box" placeholder="Search by name, email, or ID..." onkeyup="filterTable()">

    <div class="table-card">
        <table id="adminTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email Address</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($participants)): ?>
                    <tr><td colspan="5" style="text-align:center; padding: 30px; color: #64748b;">No records found.</td></tr>
                <?php else: ?>
                    <?php foreach ($participants as $row): ?>
                    <tr>
                        <td>#<?= $row['id'] ?></td>
                        <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><span class="badge badge-sent"><?= $row['status'] ?></span></td>
                        <td class="actions-cell">
                            <a href="resend.php?id=<?= $row['id'] ?>" class="btn-action btn-resend">Resend</a>
                            <a href="delete.php?id=<?= $row['id'] ?>" 
                               class="btn-action btn-delete" 
                               onclick="return confirm('WARNING: Delete this participant?')">
                               Delete
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function handleRealUpload() {
    const fileInput = document.getElementById('fileInput');
    const btn = document.getElementById('uploadBtn');
    const progContainer = document.getElementById('progContainer');
    const progBar = document.getElementById('progBar');
    const procText = document.getElementById('procText');

    if (fileInput.files.length === 0) {
        alert("Please select a PNG file first.");
        return;
    }

    const file = fileInput.files[0];

    // Client-side validation: File size (5MB)
    if (file.size > 5 * 1024 * 1024) {
        alert("File is too large. Max size is 5MB.");
        return;
    }

    const formData = new FormData();
    formData.append("new_template", file);

    const xhr = new XMLHttpRequest();

    // Setup Progress Tracking
    xhr.upload.addEventListener("progress", function(e) {
        if (e.lengthComputable) {
            const percent = Math.round((e.loaded / e.total) * 100);
            progContainer.style.display = "block";
            procText.style.display = "block";
            btn.disabled = true;
            btn.textContent = "Uploading...";
            
            progBar.style.width = percent + "%";
            procText.textContent = percent + "% Uploaded";
        }
    });

    // Handle Server Response
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                if (xhr.responseText.trim() === "success") {
                    window.location.href = "admin.php?status=uploaded";
                } else {
                    alert("Error: " + xhr.responseText);
                    resetUI();
                }
            } else {
                alert("An error occurred during upload.");
                resetUI();
            }
        }
    };

    xhr.open("POST", "admin.php", true);
    xhr.send(formData);
}

function resetUI() {
    document.getElementById('uploadBtn').disabled = false;
    document.getElementById('uploadBtn').textContent = "Upload & Refresh Template";
    document.getElementById('progContainer').style.display = "none";
    document.getElementById('procText').style.display = "none";
}

function updateFileName() {
    const input = document.getElementById('fileInput');
    const display = document.getElementById('fileNameDisplay');
    if (input.files.length > 0) {
        display.textContent = "Selected: " + input.files[0].name;
        display.style.color = "var(--blue)";
        display.style.fontWeight = "600";
    }
}

function filterTable() {
    let filter = document.getElementById("searchInput").value.toUpperCase();
    let rows = document.getElementById("adminTable").getElementsByTagName("tr");
    for (let i = 1; i < rows.length; i++) {
        let textContent = rows[i].textContent.toUpperCase();
        rows[i].style.display = textContent.includes(filter) ? "" : "none";
    }
}
</script>
</body>
</html>
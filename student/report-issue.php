<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/json.php';

// Require login
require_login('login.php');

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = get_current_user_id();
    $user = get_user_by_id($user_id, ROLE_STUDENT);
    
    if (!$user) {
        $error = 'User not found.';
    } else {
        // Process each report column (up to 3 reports)
        $reports_submitted = 0;
        
        for ($i = 1; $i <= 3; $i++) {
            $prefix = ($i === 1) ? '' : '_' . $i;
            
            $student_no = trim($_POST['student_no' . $prefix] ?? '');
            $residence = $_POST['residence' . $prefix] ?? '';
            $block = $_POST['block' . $prefix] ?? '';
            $room = $_POST['room' . $prefix] ?? '';
            $issue = $_POST['issue' . $prefix] ?? '';
            $gender = $_POST['gender' . $prefix] ?? '';
            $description = trim($_POST['description' . $prefix] ?? '');
            
            // Skip if first column is empty (no report submitted)
            if ($i === 1 && empty($student_no) && empty($residence)) {
                continue;
            }
            
            // Skip if other columns are empty
            if ($i > 1 && empty($student_no) && empty($residence)) {
                continue;
            }
            
            // Validate required fields for this report
            if (empty($student_no) || empty($residence) || empty($block) || empty($room) || empty($issue) || empty($gender)) {
                $error = "Please fill all required fields for Report $i.";
                break;
            }
            
            // Handle image upload
            $image_path = '';
            if (isset($_FILES['picture' . $prefix]) && $_FILES['picture' . $prefix]['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['picture' . $prefix];
                
                // Validate file type
                if (!in_array($file['type'], ALLOWED_IMAGE_TYPES)) {
                    $error = "Invalid image type for Report $i. Only JPG and PNG allowed.";
                    break;
                }
                
                // Validate file size
                if ($file['size'] > MAX_IMAGE_SIZE) {
                    $error = "Image too large for Report $i. Maximum 5MB allowed.";
                    break;
                }
                
                // Create upload directory if it doesn't exist
                if (!file_exists(ISSUES_PICTURE_PATH)) {
                    mkdir(ISSUES_PICTURE_PATH, 0777, true);
                }
                
                // Generate unique filename
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'issue_' . generate_id() . '.' . $extension;
                $upload_path = ISSUES_PICTURE_PATH . '/' . $filename;
                
                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $image_path = 'pictures/issues/' . $filename;
                } else {
                    $error = "Failed to upload image for Report $i.";
                    break;
                }
            }
            
            // Create maintenance request
            $request = [
                'id' => generate_id('issue_'),
                'student_id' => $user_id,
                'student_name' => $user['name'],
                'student_no' => htmlspecialchars($student_no),
                'residence' => htmlspecialchars($residence),
                'block' => htmlspecialchars($block),
                'room' => htmlspecialchars($room),
                'issue' => htmlspecialchars($issue),
                'gender' => htmlspecialchars($gender),
                'description' => htmlspecialchars($description),
                'image' => $image_path,
                'status' => STATUS_PENDING,
                'assigned_to' => null,
                'maintenance_notes' => '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if (json_add(MAINTENANCE_REQUESTS_FILE, $request)) {
                $reports_submitted++;
                // Log activity
                log_activity($user_id, ROLE_STUDENT, 'report_issue', "Reported issue: $issue", $request['id']);
            } else {
                $error = "Failed to save Report $i.";
                break;
            }
        }
        
        if ($reports_submitted > 0 && empty($error)) {
            $success = "$reports_submitted issue(s) reported successfully!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="Report maintenance issues at VUT Main Residence easily using our online website.">
    <meta name="keywords" content="VUT, Report Issue, Maintenance website, Student Support">
    <title>Report - MainRes Maintenance</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="home-page">
    <nav class="navbar">
        <input type="checkbox" id="nav-toggle" class="nav-toggle-input">
        <div class="logo">
            <img src="../assets/images/logo.png" alt="VUT Logo" class="nav-logo">
            VUT MainRes<span>Maintenance</span>
        </div>
        <label for="nav-toggle" class="menu-toggle-btn">
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
        </label>
        <ul class="nav-links" id="nav-links">
            <li><a href="../index.php">Home</a></li>
            <li><a href="../index.php?page=about">About</a></li>
            <li><a href="report-issue.php">Report</a></li>
            <li><a href="my-issues.php" class="nav-btn-text">My Issues</a></li>
            <li><a href="profile.php" class="nav-btn-text">Profile</a></li>
            <li><a href="login.php?action=logout" class="nav-btn-primary">Log Out</a></li>
        </ul>
    </nav>

    <section class="report-section page-container">
        <h2 class="section-title">Report an Issue</h2>
        <p class="section-subtitle">Found something that needs fixing? Let us know and we'll handle it.</p>
        
        <?php if ($error): ?>
            <div class="error-message" style="color: #ef4444; margin-bottom: 1rem; padding: 1rem; background: rgba(239, 68, 68, 0.1); border-radius: 8px; text-align: center;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message" style="color: #22c55e; margin-bottom: 1rem; padding: 1rem; background: rgba(34, 197, 94, 0.1); border-radius: 8px; text-align: center;">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <input type="checkbox" id="show-col-2" class="col-toggle">
        <input type="checkbox" id="show-col-3" class="col-toggle">

        <div class="report-controls">
            <label for="show-col-2" class="btn-add-report label-add-2">Add Report</label>
            <label for="show-col-3" class="btn-add-report label-add-3">Add Report</label>
            <label for="show-col-2" class="btn-remove-report label-remove-2">Remove Report</label>
            <label for="show-col-3" class="btn-remove-report label-remove-3">Remove Report</label>
        </div>

        <form action="" method="POST" enctype="multipart/form-data" class="report-form-integrated">
            <div class="table-wrapper-integrated">
                <table class="report-table-integrated">
                    <thead>
                        <tr>
                            <th class="label-col">Field Name</th>
                            <th>Report 1</th>
                            <th class="report-col-2">Report 2</th>
                            <th class="report-col-3">Report 3</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="label-col">Student NO</td>
                            <td><input type="text" name="student_no" placeholder="Enter Student NO" required></td>
                            <td class="report-col-2"><input type="text" name="student_no_2" placeholder="Enter Student NO"></td>
                            <td class="report-col-3"><input type="text" name="student_no_3" placeholder="Enter Student NO"></td>
                        </tr>
                        <tr>
                            <td class="label-col">Residence</td>
                            <td>
                                <select name="residence" required>
                                    <option value="" disabled selected>Select Residence</option>
                                    <option value="Nkandla">Nkandla</option>
                                    <option value="Malema">Malema</option>
                                    <option value="Leseding">Leseding</option>
                                    <option value="Khomanani">Khomanani</option>
                                    <option value="Meropa">Meropa</option>
                                    <option value="Khayelethu">Khayelethu</option>
                                </select>
                            </td>
                            <td class="report-col-2">
                                <select name="residence_2">
                                    <option value="" disabled selected>Select Residence</option>
                                    <option value="Nkandla">Nkandla</option>
                                    <option value="Malema">Malema</option>
                                    <option value="Leseding">Leseding</option>
                                    <option value="Khomanani">Khomanani</option>
                                    <option value="Meropa">Meropa</option>
                                    <option value="Khayelethu">Khayelethu</option>
                                </select>
                            </td>
                            <td class="report-col-3">
                                <select name="residence_3">
                                    <option value="" disabled selected>Select Residence</option>
                                    <option value="Nkandla">Nkandla</option>
                                    <option value="Malema">Malema</option>
                                    <option value="Leseding">Leseding</option>
                                    <option value="Khomanani">Khomanani</option>
                                    <option value="Meropa">Meropa</option>
                                    <option value="Khayelethu">Khayelethu</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="label-col">Block NO</td>
                            <td>
                                <select name="block" required>
                                    <option value="" disabled selected>Select Block</option>
                                    <optgroup label="Nkandla">
                                        <option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option>
                                    </optgroup>
                                    <optgroup label="Malema">
                                        <option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option>
                                    </optgroup>
                                    <optgroup label="Leseding">
                                        <option value="A">A</option><option value="B">B</option><option value="C">C</option><option value="D">D</option><option value="E">E</option><option value="F">F</option>
                                    </optgroup>
                                    <optgroup label="Meropa">
                                        <option value="A">A</option><option value="B">B</option>
                                    </optgroup>
                                    <optgroup label="Khomanani">
                                        <option value="Main">Main</option>
                                    </optgroup>
                                    <optgroup label="Khayelethu">
                                        <option value="Main">Main</option>
                                    </optgroup>
                                </select>
                            </td>
                            <td class="report-col-2">
                                <select name="block_2">
                                    <option value="" disabled selected>Select Block</option>
                                    <optgroup label="Nkandla">
                                        <option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option>
                                    </optgroup>
                                    <optgroup label="Malema">
                                        <option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option>
                                    </optgroup>
                                    <optgroup label="Leseding">
                                        <option value="A">A</option><option value="B">B</option><option value="C">C</option><option value="D">D</option><option value="E">E</option><option value="F">F</option>
                                    </optgroup>
                                    <optgroup label="Meropa">
                                        <option value="A">A</option><option value="B">B</option>
                                    </optgroup>
                                    <optgroup label="Khomanani">
                                        <option value="Main">Main</option>
                                    </optgroup>
                                    <optgroup label="Khayelethu">
                                        <option value="Main">Main</option>
                                    </optgroup>
                                </select>
                            </td>
                            <td class="report-col-3">
                                <select name="block_3">
                                    <option value="" disabled selected>Select Block</option>
                                    <optgroup label="Nkandla">
                                        <option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option>
                                    </optgroup>
                                    <optgroup label="Malema">
                                        <option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option>
                                    </optgroup>
                                    <optgroup label="Leseding">
                                        <option value="A">A</option><option value="B">B</option><option value="C">C</option><option value="D">D</option><option value="E">E</option><option value="F">F</option>
                                    </optgroup>
                                    <optgroup label="Meropa">
                                        <option value="A">A</option><option value="B">B</option>
                                    </optgroup>
                                    <optgroup label="Khomanani">
                                        <option value="Main">Main</option>
                                    </optgroup>
                                    <optgroup label="Khayelethu">
                                        <option value="Main">Main</option>
                                    </optgroup>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="label-col">Room NO</td>
                            <td>
                                <select name="room" required>
                                    <option value="" disabled selected>Select Room</option>
                                    <optgroup label="Ground Floor">
                                        <option value="G1">G1</option><option value="G2">G2</option><option value="G3">G3</option><option value="G4">G4</option><option value="G5">G5</option><option value="G6">G6</option><option value="G7">G7</option><option value="G8">G8</option><option value="G9">G9</option><option value="G10">G10</option><option value="G11">G11</option><option value="G12">G12</option>
                                    </optgroup>
                                    <optgroup label="First Floor">
                                        <option value="F1">F1</option><option value="F2">F2</option><option value="F3">F3</option><option value="F4">F4</option><option value="F5">F5</option><option value="F6">F6</option><option value="F7">F7</option><option value="F8">F8</option><option value="F9">F9</option><option value="F10">F10</option><option value="F11">F11</option><option value="F12">F12</option>
                                    </optgroup>
                                    <optgroup label="Second Floor">
                                        <option value="S1">S1</option><option value="S2">S2</option><option value="S3">S3</option><option value="S4">S4</option><option value="S5">S5</option><option value="S6">S6</option><option value="S7">S7</option><option value="S8">S8</option><option value="S9">S9</option><option value="S10">S10</option><option value="S11">S11</option><option value="S12">S12</option>
                                    </optgroup>
                                </select>
                            </td>
                            <td class="report-col-2">
                                <select name="room_2">
                                    <option value="" disabled selected>Select Room</option>
                                    <optgroup label="Ground Floor">
                                        <option value="G1">G1</option><option value="G2">G2</option><option value="G3">G3</option><option value="G4">G4</option><option value="G5">G5</option><option value="G6">G6</option><option value="G7">G7</option><option value="G8">G8</option><option value="G9">G9</option><option value="G10">G10</option><option value="G11">G11</option><option value="G12">G12</option>
                                    </optgroup>
                                    <optgroup label="First Floor">
                                        <option value="F1">F1</option><option value="F2">F2</option><option value="F3">F3</option><option value="F4">F4</option><option value="F5">F5</option><option value="F6">F6</option><option value="F7">F7</option><option value="F8">F8</option><option value="F9">F9</option><option value="F10">F10</option><option value="F11">F11</option><option value="F12">F12</option>
                                    </optgroup>
                                    <optgroup label="Second Floor">
                                        <option value="S1">S1</option><option value="S2">S2</option><option value="S3">S3</option><option value="S4">S4</option><option value="S5">S5</option><option value="S6">S6</option><option value="S7">S7</option><option value="S8">S8</option><option value="S9">S9</option><option value="S10">S10</option><option value="S11">S11</option><option value="S12">S12</option>
                                    </optgroup>
                                </select>
                            </td>
                            <td class="report-col-3">
                                <select name="room_3">
                                    <option value="" disabled selected>Select Room</option>
                                    <optgroup label="Ground Floor">
                                        <option value="G1">G1</option><option value="G2">G2</option><option value="G3">G3</option><option value="G4">G4</option><option value="G5">G5</option><option value="G6">G6</option><option value="G7">G7</option><option value="G8">G8</option><option value="G9">G9</option><option value="G10">G10</option><option value="G11">G11</option><option value="G12">G12</option>
                                    </optgroup>
                                    <optgroup label="First Floor">
                                        <option value="F1">F1</option><option value="F2">F2</option><option value="F3">F3</option><option value="F4">F4</option><option value="F5">F5</option><option value="F6">F6</option><option value="F7">F7</option><option value="F8">F8</option><option value="F9">F9</option><option value="F10">F10</option><option value="F11">F11</option><option value="F12">F12</option>
                                    </optgroup>
                                    <optgroup label="Second Floor">
                                        <option value="S1">S1</option><option value="S2">S2</option><option value="S3">S3</option><option value="S4">S4</option><option value="S5">S5</option><option value="S6">S6</option><option value="S7">S7</option><option value="S8">S8</option><option value="S9">S9</option><option value="S10">S10</option><option value="S11">S11</option><option value="S12">S12</option>
                                    </optgroup>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="label-col">ISSUE</td>
                            <td>
                                <select name="issue" required>
                                    <option value="" disabled selected>Select Issue</option>
                                    <option value="wifi">wifi</option>
                                    <option value="stove">stove</option>
                                    <option value="shower">shower</option>
                                    <option value="door handle">door handle</option>
                                    <option value="window handle">window handle</option>
                                    <option value="leakage">leakage</option>
                                    <option value="blocked sink">blocked sink</option>
                                    <option value="plug">plug</option>
                                    <option value="bulb">bulb</option>
                                    <option value="bed">bed</option>
                                    <option value="table">table</option>
                                </select>
                            </td>
                            <td class="report-col-2">
                                <select name="issue_2">
                                    <option value="" disabled selected>Select Issue</option>
                                    <option value="wifi">wifi</option>
                                    <option value="stove">stove</option>
                                    <option value="shower">shower</option>
                                    <option value="door handle">door handle</option>
                                    <option value="window handle">window handle</option>
                                    <option value="leakage">leakage</option>
                                    <option value="blocked sink">blocked sink</option>
                                    <option value="plug">plug</option>
                                    <option value="bulb">bulb</option>
                                    <option value="bed">bed</option>
                                    <option value="table">table</option>
                                </select>
                            </td>
                            <td class="report-col-3">
                                <select name="issue_3">
                                    <option value="" disabled selected>Select Issue</option>
                                    <option value="wifi">wifi</option>
                                    <option value="stove">stove</option>
                                    <option value="shower">shower</option>
                                    <option value="door handle">door handle</option>
                                    <option value="window handle">window handle</option>
                                    <option value="leakage">leakage</option>
                                    <option value="blocked sink">blocked sink</option>
                                    <option value="plug">plug</option>
                                    <option value="bulb">bulb</option>
                                    <option value="bed">bed</option>
                                    <option value="table">table</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="label-col">Gender</td>
                            <td>
                                <select name="gender" required>
                                    <option value="" disabled selected>Select Gender</option>
                                    <option value="F">F</option>
                                    <option value="M">M</option>
                                </select>
                            </td>
                            <td class="report-col-2">
                                <select name="gender_2">
                                    <option value="" disabled selected>Select Gender</option>
                                    <option value="F">F</option>
                                    <option value="M">M</option>
                                </select>
                            </td>
                            <td class="report-col-3">
                                <select name="gender_3">
                                    <option value="" disabled selected>Select Gender</option>
                                    <option value="F">F</option>
                                    <option value="M">M</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="label-col">Picture</td>
                            <td><input type="file" name="picture" accept="image/*"></td>
                            <td class="report-col-2"><input type="file" name="picture_2" accept="image/*"></td>
                            <td class="report-col-3"><input type="file" name="picture_3" accept="image/*"></td>
                        </tr>
                        <tr>
                            <td class="label-col">Description</td>
                            <td><textarea name="description" placeholder="Describe the issue..."></textarea></td>
                            <td class="report-col-2"><textarea name="description_2" placeholder="Describe the issue..."></textarea></td>
                            <td class="report-col-3"><textarea name="description_3" placeholder="Describe the issue..."></textarea></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="button-container-integrated">
                <button type="submit" class="report-btn-integrated">Submit Report</button>
            </div>
        </form>
    </section>

    <footer class="footer">
        <div class="footer-services">
            <div class="footer-column">
                <div class="footer-column-title">Our Services</div>
                <p class="footer-column-text">We offer a wide range of premium maintenance services to keep your property in perfect condition.</p>
            </div>
            <div class="footer-column">
                <div class="footer-column-title">Maintenance Services</div>
                <a href="../index.php?page=about">Bulb Replacement</a>
                <a href="../index.php?page=about">Window Handle</a>
                <a href="../index.php?page=about">Door Handle</a>
                <a href="../index.php?page=about">WiFi Problems</a>
                <a href="../index.php?page=about">Leakage Problems</a>
                <a href="../index.php?page=about">Stove Problem</a>
                <a href="../index.php?page=about">HVAC</a>
                <a href="../index.php?page=about">Painting</a>
            </div>
            <div class="footer-column">
                <div class="footer-column-title">Quick Navigation</div>
                <a href="../index.php">Home</a>
                <a href="login.php">Log In</a>
                <a href="../index.php?page=about">About</a>
                <a href="register.php">Sign Up</a>
            </div>
            <div class="footer-column">
                <div class="footer-column-title">Connect with us</div>
                <div class="social-icons">
                    <a href="https://www.facebook.com/VUT.ac.za/" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://x.com/VUT_Science" target="_blank" rel="noopener"><i class="fab fa-x-twitter"></i></a>
                    <a href="https://www.linkedin.com/school/vaal-university-of-technology/" target="_blank" rel="noopener"><i class="fab fa-linkedin-in"></i></a>
                    <a href="https://www.instagram.com/vut_university/" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>
                    <a href="https://www.tiktok.com/@vut_university" target="_blank" rel="noopener"><i class="fab fa-tiktok"></i></a>
                    <a href="https://www.youtube.com/user/VUTTV" target="_blank" rel="noopener"><i class="fab fa-youtube"></i></a>
                </div>
                <div class="footer-column-title">Official Site</div>
                <a href="https://vut.ac.za/" target="_blank" rel="noopener"><i class="fas fa-external-link-alt"></i> Visit VUT Website</a>
            </div>
        </div>
        <div class="footer-content">
            <div class="footer-brand">
                <img src="../assets/images/logo.png" alt="MainRes Logo" class="footer-logo">
                <p>&copy; 2026 MainRes Maintenance. All rights reserved.</p>
            </div>
            <div class="footer-links">
                <a href="mailto:vut@mainresmaintenance.com" class="footer-link">vut@mainresmaintenance.com</a>
            </div>
        </div>
    </footer>
</body>
</html>

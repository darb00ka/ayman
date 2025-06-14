<?php
session_start();
include '../inc/func.php'; // تأكد أن المسار صحيح لملف func.php
remember();

// التحقق من صلاحيات المسؤول
if(!$_SESSION['id'] || $_SESSION['acc_type'] != "admin"){
    header("Location: ../login.php"); // توجيه لصفحة تسجيل الدخول إذا لم يكن مسؤولاً
    exit();
}

// جلب بيانات مواقع المستخدمين المسجلين (من جدول user_locations)
$sql_user_locations = "SELECT 
                        acc.first_name, 
                        acc.last_name, 
                        acc.email, 
                        acc.id AS user_id,
                        loc.latitude, 
                        loc.longitude, 
                        loc.location_source, 
                        loc.ip_address, 
                        loc.ip_country, 
                        loc.ip_city, 
                        loc.user_agent, 
                        loc.timestamp 
                      FROM user_locations loc
                      JOIN accounts acc ON loc.user_id = acc.id 
                      ORDER BY loc.timestamp DESC";

$result_user_locations = mysqli_query($conn, $sql_user_locations);

if (!$result_user_locations) {
    $error_message_user = "حدث خطأ أثناء استرجاع بيانات مواقع المستخدمين: " . mysqli_error($conn); 
}

// جلب بيانات مواقع الزوار (من جدول guest_locations_logs)
$sql_guest_locations = "SELECT 
                         ip_address, 
                         user_agent, 
                         latitude, 
                         longitude, 
                         location_source, 
                         ip_country, 
                         ip_city, 
                         timestamp 
                       FROM guest_locations_logs
                       ORDER BY timestamp DESC";

$result_guest_locations = mysqli_query($conn, $sql_guest_locations);

if (!$result_guest_locations) {
    $error_message_guest = "حدث خطأ أثناء استرجاع بيانات مواقع الزوار: " . mysqli_error($conn); 
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم مواقع العملاء - Wastetco</title>
    <link rel="shortcut icon" href="../img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/bootstrap-rtl.css">
    <link rel="stylesheet" href="../css/all.min.css">
    <link rel="stylesheet" href="../css/main.min.css"> 
    <style>
        /* Specific styles for admin_locations.php for internal scrolling */
        /* This will apply max-height and overflow-y to the table containers */
        .tab-content .table-responsive {
            max-height: 500px; /* Maximum height before scrolling */
            overflow-y: auto; /* Enable vertical scrolling */
            border: 1px solid var(--border-color-light); /* Added border for visual separation */
            border-radius: 8px; /* Maintain rounded corners */
            margin-top: 15px; /* Spacing from tabs */
            padding: 10px; /* Internal padding */
            background-color: var(--section-background); /* White background */
            box-shadow: 0px 4px 10px var(--shadow-color); /* Light shadow */
        }
        /* Ensure tables within the scrollable div take full width */
        .tab-content .table-responsive table {
            width: 100%;
            min-width: 900px; /* Adjust min-width if table content gets too squished */
            margin-bottom: 0; /* Remove default table margin-bottom */
        }
        /* Adjust column width for user-agent for better readability */
        .user-agent-col {
            max-width: 200px; /* Limit user agent column width */
            word-wrap: break-word; /* Wrap long text */
        }
        /* Overriding specific text colors in this page */
        .page-content h1, .page-content h4 {
            color: var(--main-dark-color) !important;
        }
    </style>
</head>
<body>
    <div class="admin-page"> <?php include 'header.php'; // تضمين الهيدر الخاص بصفحات الـ pages/ ?>
        <div class="container page-content"> <h1><i class="fas fa-map-marker-alt ml-2"></i> سجلات المواقع الجغرافية</h1>
            
            <ul class="nav nav-tabs" id="locationTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="user-locations-tab" data-toggle="tab" href="#user-locations" role="tab" aria-controls="user-locations" aria-selected="true">مواقع المستخدمين المسجلين</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="guest-locations-tab" data-toggle="tab" href="#guest-locations" role="tab" aria-controls="guest-locations" aria-selected="false">مواقع الزوار (غير المسجلين)</a>
                </li>
            </ul>

            <div class="tab-content" id="locationTabsContent">
                <div class="tab-pane fade show active" id="user-locations" role="tabpanel" aria-labelledby="user-locations-tab">
                    <h4 class="mt-3 mb-2">سجلات مواقع المستخدمين المسجلين</h4>
                    <?php if (isset($error_message_user)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message_user); ?></div>
                    <?php endif; ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>م</th>
                                    <th>الاسم الكامل</th>
                                    <th>البريد الإلكتروني</th>
                                    <th>ID المستخدم</th>
                                    <th>معلومات الجهاز (User Agent)</th> 
                                    <th>خط العرض (دقيق)</th>
                                    <th>خط الطول (دقيق)</th>
                                    <th>مصدر الموقع</th>
                                    <th>عنوان IP</th>
                                    <th>البلد (من IP)</th>
                                    <th>المدينة (من IP)</th>
                                    <th>آخر تحديث</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (isset($result_user_locations) && $result_user_locations && mysqli_num_rows($result_user_locations) > 0) {
                                    $n = 1;
                                    while ($row = mysqli_fetch_assoc($result_user_locations)) {
                                        // جلب الاسم الكامل من user_id عبر دالة is_user
                                        $user_full_name_data = is_user($conn, $row['user_id']);
                                        $user_full_name = "غير معروف";
                                        if (!$user_full_name_data['error'] && $user_full_name_data['check']) {
                                            $user_full_name = short_name($user_full_name_data['user']);
                                        }

                                        echo "<tr>";
                                        echo "<td>" . $n++ . "</td>";
                                        echo "<td><a href='manage_account.php?id=" . htmlspecialchars($row['user_id']) . "' class='text-dark font-weight-bold'>" . $user_full_name . "</a></td>";
                                        echo "<td>" . htmlspecialchars($row['email'] ?? '-') . "</td>";
                                        echo "<td>" . htmlspecialchars($row['user_id'] ?? '-') . "</td>";
                                        echo "<td class='user-agent-col'>" . htmlspecialchars($row['user_agent'] ?? '-') . "</td>"; 
                                        echo "<td>" . ($row['latitude'] ? htmlspecialchars(sprintf("%.6f", $row['latitude'])) : '-') . "</td>"; 
                                        echo "<td>" . ($row['longitude'] ? htmlspecialchars(sprintf("%.6f", $row['longitude'])) : '-') . "</td>";
                                        echo "<td>" . htmlspecialchars($row['location_source'] ?? '-') . "</td>";
                                        echo "<td>" . htmlspecialchars($row['ip_address'] ?? '-') . "</td>";
                                        echo "<td>" . htmlspecialchars($row['ip_country'] ?? '-') . "</td>";
                                        echo "<td>" . htmlspecialchars($row['ip_city'] ?? '-') . "</td>";
                                        
                                        $timestamp_val = strtotime($row['timestamp']);
                                        $formatted_time = 'غير متوفر';
                                        if ($timestamp_val) {
                                            $formatted_time = arabicDate($timestamp_val) . " - " . timeHour($timestamp_val);
                                        }
                                        echo "<td>" . $formatted_time . "</td>";
                                        echo "</tr>";
                                    }
                                } elseif (!isset($error_message_user)) {
                                    echo '<tr><td colspan="12" class="text-center alert alert-info">لا توجد بيانات مواقع لمستخدمين مسجلين حالياً.</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="guest-locations" role="tabpanel" aria-labelledby="guest-locations-tab">
                    <h4 class="mt-3 mb-2">سجلات مواقع الزوار (غير المسجلين)</h4>
                    <?php if (isset($error_message_guest)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message_guest); ?></div>
                    <?php endif; ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>م</th>
                                    <th>عنوان IP</th>
                                    <th>معلومات الجهاز (User Agent)</th>
                                    <th>خط العرض (دقيق)</th>
                                    <th>خط الطول (دقيق)</th>
                                    <th>مصدر الموقع</th>
                                    <th>البلد (من IP)</th>
                                    <th>المدينة (من IP)</th>
                                    <th>وقت الزيارة</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (isset($result_guest_locations) && $result_guest_locations && mysqli_num_rows($result_guest_locations) > 0) {
                                    $n = 1;
                                    while ($row = mysqli_fetch_assoc($result_guest_locations)) {
                                        echo "<tr>";
                                        echo "<td>" . $n++ . "</td>";
                                        echo "<td>" . htmlspecialchars($row['ip_address'] ?? '-') . "</td>";
                                        echo "<td class='user-agent-col'>" . htmlspecialchars($row['user_agent'] ?? '-') . "</td>"; 
                                        echo "<td>" . ($row['latitude'] ? htmlspecialchars(sprintf("%.6f", $row['latitude'])) : '-') . "</td>";
                                        echo "<td>" . ($row['longitude'] ? htmlspecialchars(sprintf("%.6f", $row['longitude'])) : '-') . "</td>";
                                        echo "<td>" . htmlspecialchars($row['location_source'] ?? '-') . "</td>";
                                        echo "<td>" . htmlspecialchars($row['ip_country'] ?? '-') . "</td>";
                                        echo "<td>" . htmlspecialchars($row['ip_city'] ?? '-') . "</td>";
                                        
                                        $timestamp_val = strtotime($row['timestamp']);
                                        $formatted_time = 'غير متوفر';
                                        if ($timestamp_val) {
                                            $formatted_time = arabicDate($timestamp_val) . " - " . timeHour($timestamp_val);
                                        }
                                        echo "<td>" . $formatted_time . "</td>";
                                        echo "</tr>";
                                    }
                                } elseif (!isset($error_message_guest)) {
                                    echo '<tr><td colspan="9" class="text-center alert alert-info">لا توجد سجلات مواقع للزوار حالياً.</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../footer.php'; // تضمين الفوتر من المجلد الرئيسي ?>
    <script src="../js/popper.min.js"></script>
    <script src="../js/ajax.min.js"></script> <script src="../js/bootstrap.min.js"></script>
    <script src="../js/aos.js"></script>
    <script src="../js/main.js"></script>
    <script src="../js/notifications.js"></script>
    <script>
        AOS.init(); // التأكد من تهيئة AOS
        $(function() {
            // تفعيل تبويبات Bootstrap يدوياً
            $('#locationTabs a').on('click', function (e) {
                e.preventDefault();
                $(this).tab('show');
            });
        });
    </script>
</body>
</html>
<?php
// إغلاق اتصال قاعدة البيانات إذا كان لا يزال مفتوحًا
if (isset($conn)) {
    mysqli_close($conn);
}
?>
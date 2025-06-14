<?php
// هذا الملف يحتوي على منطق تحديث الموقع الجغرافي للمستخدم
// يجب تضمين هذا الملف في func.php ليتم استدعاء الدالة منه

// دالة لتحديث الموقع الجغرافي للمستخدم المسجل في قاعدة البيانات
// هذه الدالة ستحاول أولاً الحصول على الموقع من IP (إذا لم يتوفر من المتصفح)
// ثم ستقوم بتحديث السجل الخاص بالمستخدم في جدول user_locations
function update_user_location($conn, $user_id, $ip_address, $user_agent, $browser_latitude = null, $browser_longitude = null) {
    
    $latitude = $browser_latitude;
    $longitude = $browser_longitude;
    $location_source = 'ip'; // افتراضيًا، المصدر هو IP
    $country = null;
    $city = null;

    // 1. إذا كان الموقع الدقيق متوفر من المتصفح
    if ($browser_latitude !== null && $browser_longitude !== null) {
        $location_source = 'browser';
    } else {
        // 2. إذا لم يكن متوفر، حاول الحصول على الموقع من IP
        if ($ip_address && $ip_address !== '127.0.0.1' && $ip_address !== '::1') { // تجنب IPs المحلية
            // استخدم HTTP بدلاً من HTTPS إذا كانت cURL تواجه مشاكل SSL
            // ملاحظة: استخدام HTTP لـ ip-api.com قد يكون له قيود، الأفضل هو HTTPS إذا كان الخادم يدعمه.
            $ip_api_url = "http://ip-api.com/json/" . $ip_address; 
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $ip_api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5); // مهلة 5 ثوانٍ لطلب الـ API
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response && $http_code == 200) {
                $geo_data = json_decode($response, true);
                if ($geo_data && isset($geo_data['status']) && $geo_data['status'] == 'success') {
                    $country = $geo_data['country'] ?? null;
                    $city = $geo_data['city'] ?? null;
                    // استخدم خطوط الطول والعرض من IP-API إذا لم تكن متوفرة من المتصفح
                    if ($latitude === null) $latitude = $geo_data['lat'] ?? null;
                    if ($longitude === null) $longitude = $geo_data['lon'] ?? null;
                    $location_source = 'ip';
                } else {
                    $location_source = 'ip_failed'; // فشل الحصول على بيانات صالحة من API
                }
            } else {
                $location_source = 'ip_failed'; // فشل الاتصال بخدمة API أو رمز حالة غير 200
            }
        } else {
            $location_source = 'ip_local'; // IP محلي (127.0.0.1 أو ::1)
        }
    }

    // تحديث أو إدخال بيانات الموقع في جدول `user_locations`
    // تم إضافة `user_agent` هنا في جملة VALUES و ON DUPLICATE KEY UPDATE
    $sql = "INSERT INTO user_locations (user_id, latitude, longitude, location_source, ip_address, user_agent, ip_country, ip_city) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                latitude = VALUES(latitude), 
                longitude = VALUES(longitude), 
                location_source = VALUES(location_source), 
                ip_address = VALUES(ip_address), 
                user_agent = VALUES(user_agent),
                ip_country = VALUES(ip_country), 
                ip_city = VALUES(ip_city),
                timestamp = CURRENT_TIMESTAMP"; 

    $stmt = mysqli_stmt_init($conn);
    if (mysqli_stmt_prepare($stmt, $sql)) {
        // 'd' لـ decimal (double)
        mysqli_stmt_bind_param($stmt, 'idssssss', $user_id, $latitude, $longitude, $location_source, $ip_address, $user_agent, $country, $city);
        if (!mysqli_stmt_execute($stmt)) {
            error_log("Error updating user_locations for user_id $user_id: " . mysqli_error($conn));
        }
    } else {
        error_log("Error preparing user_locations statement: " . mysqli_error($conn));
    }
}

// دالة لتحديث الموقع الجغرافي للزوار (الضيوف) في قاعدة البيانات
function update_guest_location($conn, $ip_address, $user_agent, $browser_latitude = null, $browser_longitude = null) {
    
    $latitude = $browser_latitude;
    $longitude = $browser_longitude;
    $location_source = 'ip'; // افتراضيًا، المصدر هو IP
    $country = null;
    $city = null;

    // 1. إذا كان الموقع الدقيق متوفر من المتصفح
    if ($browser_latitude !== null && $browser_longitude !== null) {
        $location_source = 'browser';
    } else {
        // 2. إذا لم يكن متوفر، حاول الحصول على الموقع من IP
        if ($ip_address && $ip_address !== '127.0.0.1' && $ip_address !== '::1') { // تجنب IPs المحلية
            $ip_api_url = "http://ip-api.com/json/" . $ip_address; 
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $ip_api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5); // مهلة 5 ثوانٍ لطلب الـ API
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response && $http_code == 200) {
                $geo_data = json_decode($response, true);
                if ($geo_data && isset($geo_data['status']) && $geo_data['status'] == 'success') {
                    $country = $geo_data['country'] ?? null;
                    $city = $geo_data['city'] ?? null;
                    if ($latitude === null) $latitude = $geo_data['lat'] ?? null;
                    if ($longitude === null) $longitude = $geo_data['lon'] ?? null;
                    $location_source = 'ip';
                } else {
                    $location_source = 'ip_failed'; 
                }
            } else {
                $location_source = 'ip_failed'; 
            }
        } else {
            $location_source = 'ip_local'; 
        }
    }

    // إدخال بيانات الموقع في جدول `guest_locations_logs`
    // هنا لا نستخدم ON DUPLICATE KEY UPDATE لأننا نريد تسجيل كل زيارة جديدة
    $sql = "INSERT INTO guest_locations_logs (ip_address, user_agent, latitude, longitude, location_source, ip_country, ip_city) 
            VALUES (?, ?, ?, ?, ?, ?, ?)"; 

    $stmt = mysqli_stmt_init($conn);
    if (mysqli_stmt_prepare($stmt, $sql)) {
        mysqli_stmt_bind_param($stmt, 'ssddsss', $ip_address, $user_agent, $latitude, $longitude, $location_source, $country, $city);
        if (!mysqli_stmt_execute($stmt)) {
            error_log("Error inserting guest_locations_log: " . mysqli_error($conn));
        }
    } else {
        error_log("Error preparing guest_locations_log statement: " . mysqli_error($conn));
    }
}
?>
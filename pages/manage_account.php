<?php
session_start();
include '../inc/func.php';
remember();
if(!$_SESSION['id'] || $_SESSION['acc_type'] != "admin"){
    header("Location: ../login.php"); // تأكد من مسار صفحة تسجيل الدخول
    exit();
}else{
    $id = safe($_GET['id'], 'int');
    $balance = balance($conn, $id);
    $available = $balance['available'];
    $banned = $balance['banned'];

    // جلب بيانات الموقع الخاصة بهذا المستخدم
    $user_location_data = get_user_location_details($conn, $id);
    $location_info = null;
    if (!$user_location_data['error'] && $user_location_data['check']) {
        $location_info = $user_location_data['location'];
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    
    <head>
        <?php include 'head.php'?>
    </head>
    
    <body>
        <div class="account-page">
            <?php include 'header.php' ?>
            <div class="container">
                <div class="modal fade" id="notiModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLongTitle">تنبيه</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body" id="notiBody">
    
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                            </div>
                        </div>
                    </div>
                </div>
                <section class="balance-summary text-center rounded">
                    <div class="container">
                        <div class="row">
                            <div class="col-sm-6 col-md-4 balance-area">
                                <h6>الرصيد المتاح</h6>
                                <h5><span id="availableBalance"><?php echo $available ?></span> جنيه مصري</h5>
                            </div>
                            <div class="col-sm-6 col-md-4 balance-area">
                                <h6>الرصيد المرهون لدى الموقع</h6>
                                <h5><span id="bannedBalance"><?php echo $banned ?></span> جنيه مصري</h5>
                            </div>
                            <div class="col-md-4 balance-area" style="border: none;">
                                <h6>إجمالي الرصيج</h6>
                                <h5><span class="total-balance"><?php echo $available + $banned ?></span> جنيه مصري</h5>
                            </div>
                        </div>
                    </div>
                </section>
                <div class="container">
                    <div class="row">
                        <div class="col-md-8">
                            <ul class="nav nav-tabs mt-4">
                                <li class="nav-item">
                                    <a class="nav-link content-switch active" href="#" id="btn-profile">تحديث معلومات الحساب</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link content-switch" href="#" id="btn-purches">المشتريات</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link content-switch" href="#" id="btn-sales">المبيعات</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link content-switch" href="#" id="btn-money">المعاملات المالية</a>
                                </li>
                            </ul>
                            <section class="page-content">
                                <section class="profile" id="profile">
                                    <h4 class="mt-3 mb-2">المعلومات الشخصية</h4>
                                    <div class="container">
                                        <?php
                                        $user = is_user($conn, $id);
                                        if($user['error']){
                                            sendError($user['message']);
                                        }else{
                                            $user = $user['user'];
                                            ?>
                                            <div class="row">
                                                <?php
                                                if($user['verified'] == "yes"){
                                                    ?>
                                                    <p class="lead font-weight-bold mb-3 mt-3"><i class="fas fa-check-circle ml-2 text-success"></i> هذا العميل موثق</p>
                                                    <?php
                                                }
                                                ?>
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>الإسم الأول</label>
                                                        <input type="text" class="form-control" id="firstname" disabled value="<?=$user['first_name']?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>الإسم الأخير</label>
                                                        <input type="text" class="form-control" id="lastname" disabled value="<?=$user['last_name']?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>الرقم القومي</label>
                                                        <input type="text" class="form-control" id="nid" disabled value="<?=$user['nid']?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>البريد الإلكتروني</label>
                                                        <input type="text" class="form-control" id="email" disabled value="<?=$user['email']?>">
                                                    </div>
                                                </div>
                                            </div> <?php if ($location_info): ?>
                                            <h5 class="mt-3 mb-2">معلومات الموقع الجغرافي</h5>
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>عنوان IP:</label>
                                                        <input type="text" class="form-control" value="<?= htmlspecialchars($location_info['ip_address'] ?? 'غير متوفر') ?>" disabled>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>مصدر الموقع:</label>
                                                        <input type="text" class="form-control" value="<?= htmlspecialchars($location_info['location_source'] ?? 'غير متوفر') ?>" disabled>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>البلد:</label>
                                                        <input type="text" class="form-control" value="<?= htmlspecialchars($location_info['ip_country'] ?? 'غير متوفر') ?>" disabled>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>المدينة:</label>
                                                        <input type="text" class="form-control" value="<?= htmlspecialchars($location_info['ip_city'] ?? 'غير متوفر') ?>" disabled>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>خط الطول (دقيق):</label>
                                                        <input type="text" class="form-control" value="<?= htmlspecialchars($location_info['latitude'] ? sprintf("%.6f", $location_info['latitude']) : 'غير متوفر') ?>" disabled>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>خط العرض (دقيق):</label>
                                                        <input type="text" class="form-control" value="<?= htmlspecialchars($location_info['longitude'] ? sprintf("%.6f", $location_info['longitude']) : 'غير متوفر') ?>" disabled>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>آخر تحديث للموقع:</label>
                                                        <input type="text" class="form-control" value="<?= htmlspecialchars(arabicDate(strtotime($location_info['timestamp'])) . ' - ' . timeHour(strtotime($location_info['timestamp']))) ?>" disabled>
                                                    </div>
                                                </div>
                                            </div> <?php endif; ?>

                                            <h5 class="mt-3 mb-2">تحديث معلومات الاتصال</h5> 
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>رقم الهاتف</label>
                                                        <input type="text" class="form-control" id="phone" value="<?=$user['phone']?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <input type="text" value="<?=$user['id']?>" id="id" hidden>
                                                        <button class="btn btn-success" type="button" id="updatePhone"
                                                        style="margin-top:32px;"><i class="fas fa-check ml-2"></i>تحديث
                                                        البيانات</button>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>كلمة السر الجديدة</label>
                                                        <input type="text" id="pwd" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <button class="btn btn-success" user="<?=$id?>" style="margin-top: 30px;" type="button" id="updatePwd"><i class="fas fa-check ml-2"></i> تحديث كلمة السر</button>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>البريد الإلكتروني</label>
                                                        <input type="text" class="form-control" id="email" value="<?=$user['email']?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <button class="btn btn-success" user="<?=$id?>" style="margin-top: 30px;" type="button" id="updateEmail"><i class="fas fa-check ml-2"></i> تحديث البريد الإلكتروني</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php 
                                        }
                                        ?>
                                    </div>
                                </section>
                                <section class="purches" id="purches">
                                    <h4 class="mt-3 mb-2">المشتريات</h4>
                                    <div class="container">
                                        <?php
                                        $purches_orders = orders($conn, $id, 'purches', '');
                                        if($purches_orders['error']){
                                            sendError($purches_orders['message']);
                                        }else{
                                            if($purches_orders['num'] == 0){
                                                echo "<p class='lead text-center font-weight-bold'>لا يوجد لديك مشتريات حتى الآن</p>";
                                            }else{
                                                foreach($purches_orders['orders'] as $purches_process){
                                                    echo purches_item($conn, $purches_process);
                                                }
                                            }
                                        }
                                        ?>
                                    </div>
                                </section>
                                <section class="sales" id="sales">
                                    <h4 class="mt-3 mb-2">المبيعات</h4>
                                    <div class="container">
                                    <?php
                                        $sales_orders = orders($conn, $id, 'sales', '');
                                        if($sales_orders['error']){
                                            sendError($sales_orders['message']);
                                        }else{
                                            if($sales_orders['num'] == 0){
                                                echo "<p class='lead text-center font-weight-bold'>لا يوجد لديك مشتريات حتى الآن</p>";
                                            }else{
                                                foreach($sales_orders['orders'] as $sales_process){
                                                    echo sales_item($conn, $sales_process);
                                                }
                                            }
                                        }
                                        ?>
                                    </div>
                                </section>
                                <section class="money" id="money">
                                    <h4 class="mt-3 mb-2">المعاملات المالية</h4>
                                    <div class="container">
                                        <table class="table borderless textcenter">
                                            <thead>
                                                <tr>
                                                    <th>نوع المعاملة</th>
                                                    <th>قيمة المعاملة</th>
                                                    <th>السبب</th>
                                                    <th>التاريخ</th>
                                                    <th>الوقت</th>
                                                </tr>
                                            </thead>
                                            <?php
                                            $finance_history = finance_process($conn, $id, 'all');
                                            if($finance_history['error']){
                                                sendError($finance_history['message']);
                                            }else{
                                                if($finance_history['check'] == 0){
                                                    emptyRow(5, 'لا يوجد أي معاملات مالية لهذا الحساب حتى الان');
                                                }else{
                                                    foreach($finance_history['process'] as $finance_process){
                                                        echo process_row($finance_process);
                                                    }
                                                }
                                            }
                                            ?>
                                        </table>
                                    </div>
                                </section>
                            </section>
    
                        </div>
                        <div class="col-md-4">
                            <?php include 'sidebar.php';?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script src="../js/popper.min.js"></script>
        <script src="../js/ajax.min.js"></script>
        <script src="../js/bootstrap.min.js"></script>
        <script src="../js/aos.js"></script>
        <script src="../js/main.js"></script>
        <script src="../js/notifications.js"></script>
        <script>
        AOS.init();
        $(function() {
            $("#purches, #sales, #money").hide();
            var vodafone = "01016421977";
    
            $("#updatePhone").click(() => {
                var phone = $("#phone").val(),
                    id    = $("#id").val();
                if (!phone || !id) {
                    notiModal(`<i class="fas fa-times text-danger ml-2"></i>يجب إدخال رقم هاتف`);
                } else {
                    $.post("../inc/run.php", {
                        target: "admin-update-phone",
                        id: id,
                        phone: phone
                    }, (data) => {
                        var data = JSON.parse(data);
                        if (data.error) {
                            notiModal(
                                `<i class="fas fa-times text-danger ml-2"></i>${data.message}`);
                        } else {
                            notiModal(
                                `<i class="fas fa-check text-success ml-2"></i>تم تحديث بيانات الحساب بنجاح`
                            );
                        }
                    })
                }
            })
            $("#addModal").click(() => {
                notiModal(`<h4>طلب ايداع فى حسابك</h4>
                    <p class="text-secondary">
                        أهلاً بك، لإضافة رصيد يجب عليك تحويل المبلغ المراد إضافته إلى حساب فودافون كاش الخاص بالرقم : (${vodafone}) ، بعد ذلك تدخل كود عملية التحويل في الحقل أدناه والضغط على "تأكيد الطلب" وسيتم خلال 12 ساعة إضافة المال إلى رصيدك
                    </p>
                    <div class="container">
                        <div class="row">
                            <div class="col-8">
                                <div class="form-group">
                                    <label for="code">كود العملية:</label>
                                    <input type="text" class="form-control" id="code" placeholder="إدخل كود عملية التحويل هنا...">
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <button class="btn btn-sm btn-block btn-success" type="button" id="requestAdd" style="margin-top: 35px;"><i class="fas fa-check ml-1"></i>تأكيد الطلب</button>
                                </div>
                            </div>
                        </div>
                    </div>`);
    
                $("#requestAdd").click(function() {
                    var code = $("#code").val();
                    if (!code) {
                        $("#notiModal").modal('hide');
                        notiModal(
                            `<i class="fas fa-times text-danger ml-1"></i>يجب إدخال كود عملية التحويل`
                        );
                    } else {
                        $.post("../inc/run.php", {
                            target: "add-request",
                            code: code
                        }, (data) => {
                            var data = JSON.parse(data);
                            if (data.error) {
                                notiModal(
                                    `<i class="fas fa-times text-danger ml-2"></i>${data.message}`
                                );
                            } else {
                                notiModal(
                                    `<i class="fas fa-check text-success ml-2"></i>${data.message}`
                                );
                            }
                        })
                    }
                })
            })
    
            $("#withdrawModal").click(() => {
                notiModal(`<h4>طلب سحب رصيد</h4>
                    <p class="text-secondary">
                        يمكنك سحب أرصدتك في أي وقت من خلال إدخال قيمة المبلغ المراد سحبه في الحقل أدناه بشرط أن يكون المبلغ متوفر في خانة رصيدك المتاح وسيتم تحويل المبلغ إليكم وخصمه من حسابكم خلال مدة أقصاها 24 ساعة
                    </p>
                    <div class="container">
                        <div class="row">
                            <div class="col-8">
                                <div class="form-group">
                                    <label for="withdrawValue">المبلغ المراد سحبه:</label>
                                    <input type="text" class="form-control" id="withdrawValue" placeholder="إدخل قيمة المبلغ المراد سحبه من رصيدك المتاح">
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <button class="btn btn-sm btn-block btn-success" id="requestWithdraw" type="button" style="margin-top:35px;"><i class="fas fa-check ml-1"></i>تأكيد الطلب</button>
                                </div>
                            </div>
                        </div>
                    </div>`);
    
                $("#requestWithdraw").click(() => {
                    var value = $("#withdrawValue").val();
                    if (!value) {
                        $("#notiModal").modal('hide');
                        notiModal(
                            `<i class="fas fa-times text-danger ml-1"></i>يجب إدخال كود عملية التحويل`
                        );
                    } else {
                        $.post('../inc/run.php', {
                            target: 'withdraw-request',
                            value: value
                        }, (data) => {
                            var data = JSON.parse(data);
                            if (data.error) {
                                notiModal(
                                    `<i class="fas fa-times text-danger ml-2"></i>${data.message}`
                                );
                            } else {
                                notiModal(
                                    `<i class="fas fa-check text-success ml-2"></i>${data.message}`
                                );
                                $("#availableBalance").text(data.available);
                                $("#bannedBalance").text(data.banned);
                            }
                        })
                    }
                })
            })
            // Shifting tabs content
            $(".content-switch").click(function(e) {
                e.preventDefault();
                var $this = $(this),
                    id = $this.attr('id');
                tabName = id.split('-'),
                    tabName = `#${tabName[1]}`;
                $(tabName).siblings().hide();
                $(tabName).fadeIn(600);
                $this.parent().siblings().children().removeClass("active");
                $this.addClass("active");
            })
            $("#updatePwd").click(function (){
                    var $this = $(this),
                        user = $this.attr("user"),
                        pwd = $("#pwd").val();
                    $.post("../inc/run.php", {target: "update-pwd", user: user, pwd: pwd}, (data)=>{
                        var data = JSON.parse(data);
                        if(data.error){
                            notiModal(`<i class="fas fa-times text-danger ml-2"></i> ${data.message}`);
                        }else{
                            notiModal(`<i class="fas fa-check text-success ml-2"></i> ${data.message}<br>`);
                        }
                    })
                })
            
            $("#updateEmail").click(function (){
                var $this = $(this),
                    user = $this.attr("user"),
                    email = $("#email").val();
                $.post("../inc/run.php", {target: "update-email", user: user, email: email}, (data)=>{
                    var data = JSON.parse(data);
                    if(data.error){
                        notiModal(`<i class="fas fa-times text-danger ml-2"></i> ${data.message}`);
                    }else{
                        notiModal(`<i class="fas fa-check text-success ml-2"></i> ${data.message}<br>`);
                    }
                })
            })
        })
        </script>
    </body>
    
    </html>
    <?php
}
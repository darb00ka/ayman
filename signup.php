<?php 
session_start();
include 'inc/func.php';
if(!empty($_SESSION['id'])){
    ?>
    <script>
        window.location.replace("pages/index");
    </script>
    <?php
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title>واسطيتكو - Wastetco</title>
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/bootstrap-rtl.css">
    <link rel="stylesheet" href="./css/all.min.css">
    <link rel="stylesheet" href="./css/main.min.css">
</head>

<body>
    <div class="page-info">
        <?php include 'header.php' ?>
        <div class="container">
            <!-- Noti Modal -->
            <div class="modal fade" id="notiModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
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
            <h1>إنشاء حساب جديد</h1>
            <p>يمكنك إنشاء حساب مجاني بالكامل عبر ملئ النموذج أدناه، بعدها تستلم بريد إلكتروني يفيد أن بياناتك قيد
                المراجعة وبمجرد الموافقة على حسابك يرسل إليك بريد إلكتروني يفيد بذلك (عادة مراجعة البيانات والموافقة على
                الحساب في أقل من 24 ساعة)</p>
            <?php
            if (isset($_POST['signup'])) {
                $first_name = safe($_POST['firstname'], 'string');
                $last_name = safe($_POST['lastname'], 'string');
                $email = safe($_POST['email'], 'mail');
                $phone = safe($_POST['phone'], 'string');
                $pwd1 = safe($_POST['pwd1'], 'string');
                $pwd2 = safe($_POST['pwd2'], 'string');
                $acceptterms = safe($_POST['acceptterms'], 'string');
                $check_email = check_email($conn, $email);
                if(empty($acceptterms)){
                    sendError("لا يمكن إكمال عملية التسجيل بدون الموافقة على شروط الخدمة وسياسة الخصوصية");
                }else{
                    if(!$first_name || !$last_name || !$email || !$phone || !$pwd1 || !$pwd2 || !$last_name){
                        sendError("يجب ملئ كافة الحقول أدناه قبل إكمال عملية التسجيل");
                    }
                    if ($check_email['check']) {
                        sendError("هذا البريد الإلكتروني مستخدم بالفعل، يرجى إستخدام بريد إلكتروني أخر");
                    } else {
                        if ($pwd1 != $pwd2) {
                            sendError("كلمتين السر غير متطابقتين يرجى إعادة إدخالهم ثم المحاولة مجدداً");
                        } else {
                            $pwd = $pwd1;
                            if (strlen($pwd) < 8) {
                                sendError("لا يمكن ان تكون كلمة السر اقل من 8 أرقام / حروف");
                            } else {
                                $signup = signup($conn, $first_name, $last_name, $email, $phone, $pwd);
                                if ($signup['error']) {
                                    sendError($signup['message']);
                                } else {
                                    $file = $_FILES['idcard'];
                                    $id_card = id_card_upload($signup['id'], $file);
                                    if ($id_card['error']) {
                                        sendError($id_card['message']);
                                        remove_row($conn, 'accounts', $signup['id']);
                                    } else {
                                        sendSucc("تم إنشاء الحساب بنجاح وهو قيد المراجعة الأن، عند إنتهاء عملية المراجعة");
                                    }
                                }
                            }
                        }
                    }
                }
            }
            ?>
            <form method="post" class="pb-4" enctype="multipart/form-data">
                <div class="form-group">
                    <div class="container-fuid">
                        <div class="row">
                            <div class="col-6">
                                <label for="firstname">الإسم الأول</label>
                                <input type="text" name="firstname" class="form-control" id="firstname" placeholder="إدخل إسمك الأول هنا">
                            </div>
                            <div class="col-6">
                                <label for="lastname">الإسم الأخير</label>
                                <input type="text" name="lastname" class="form-control" id="lastname" placeholder="إدخل إسمك الأخير هنا">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="email">البريد الإلكتروني:</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="axxxxxx@gmail.com">
                </div>
                <div class="form-group">
                    <label for="phone">رقم الهاتف</label>
                    <input type="text" name="phone" id="phone" class="form-control" placeholder="0101xxxxxxxx">
                </div>
                <div class="form-group">
                    <label for="pwd1">كلمة السر:</label>
                    <input type="password" name="pwd1" id="pwd1" class="form-control" placeholder="إدخل كلمة السر هنا">
                </div>
                <div class="form-group">
                    <label for="pwd2">تأكيد كلمة السر:</label>
                    <input type="password" name="pwd2" id="pwd2" class="form-control" placeholder="أعد إدخال كلمة السر هنا مرة اخرى للتأكيد">
                </div>
                <div class="form-group">
                    <label for="idcard">صورة بطاقة الرقم القومي (الوجه الأمامي)</label>
                    <input type="file" name="idcard" id="idcard" class="form-control form-control-file">
                </div>
                <div class="form-group">
                    <input type="checkbox" name="acceptterms" value="yes">
                    <label>أوافق على <a href="terms">شروط الخدمة</a> و <a href="privacy">سياسة الخصوصية</a></label>
                </div>
                <div class="form-group">
                    <input type="text" name="target" value="signup" hidden>
                    <button class="btn btn-info grad-btn" name="signup" type="submit">إنشاء حساب</button>
                </div>
            </form>
        </div>
    </div>
    <?php include 'footer.php' ?>
    <script src="./js/popper.min.js"></script>
    <script src="./js/ajax.min.js"></script>
    <script src="./js/bootstrap.min.js"></script>
    <script src="./js/aos.js"></script>
    <script src="./js/main.js"></script>
    <script>
        AOS.init();
        $(function() {
        })
    </script>
</body>

</html>
<?php
require 'inc/func.php';
session_start();
remember();
if (!empty($_SESSION['email'])) {
    ?>
        <script>
            window.location.replace("pages/index");
        </script>
    <?php
}
?>
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
    <style>
        footer {
            margin-top: 0px !important;
        }
    </style>
</head>

<body id="loginBg">
    <div class="overlay">
        <div class="page-info">
            <?php include 'header.php' ?>
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
            <div class="container" id="loginContent">
                <div class="login-box mt-5 mb-4 text-center rounded">
                    <div class="container">
                        <h2>تسجيل الدخول</h2>
                        <p class="d-block mt-3 text-light">
                            سجل الدخول الآن بواسطة البريد الإلكتروني وكلمة السر الخاصة بك
                        </p>
                        <?php
                        if (isset($_POST['login'])) {
                            $email = safe($_POST['email'], 'mail');
                            $pwd = safe($_POST['pwd'], 'string');
                            // تعديل هنا: التحقق من وجود 'remember' قبل الوصول إليه
                            $remember = isset($_POST['remember']) ? safe($_POST['remember'], 'string') : '';
                            if (empty($email) || empty($pwd)) {
                                sendError("يجب إدخال كافة البيانات");
                            } else {
                                $login = login($conn, $email, $pwd, $remember);
                                if ($login['error']) {
                                    sendError($login['message']);
                                } else {
                                    if ($_SESSION['account_status'] == "Waiting") {
                                        sendError("مازال حسابك قيد المراجعة، سيتم الإنتهاء منه خلال 24 ساعه وعندها ستستلم بريد إلكتروني يفيد بتفعيل حسابك، في حالة قابلتك أي مشاكل يمكنك التواصل معنا من خلال <a href='index#contactUs'>الضغط هنا</a>");
                                    } else {
                                        if ($_SESSION['account_status'] == "Banned") {
                                            sendError("هذا الحساب محظور في حالة وجود مشكلة يمكنك التواصل معنا من خلال <a href='index#contactUs'>الضغط هنا</a>");
                                        } else {
                        ?>
                                            <script>
                                                window.location.replace("<?= $login['url'] ?>");
                                            </script>
                        <?php
                                        }
                                    }
                                }
                            }
                        }
                        ?>
                        <form role="form" method="POST">
                            <div class="form-group">
                                <label class="float-left" for="email"><i class="fas fa-envelope ml-2"></i> البريد
                                    الإلكتروني:</label>
                                <input type="text" name="target" value="login" hidden>
                                <input type="email" placeholder="إدخل البريد الإلكتروني الخاص بك هنا" name="email" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="float-left" for="pwd"><i class="fas fa-key ml-2"></i> كلمة السر:</label>
                                <input type="password" name="pwd" class="form-control" placeholder="إدخل كلمة السر الخاصة بك هنا">
                                
                                <input type="checkbox" name="remember" value="remember" class="ml-2 mt-3"><label for="remeber">تذكرني</label>
                            </div>
                            <div class="form-group mt-1">
                                <button class="btn btn-light btn-block" type="submit" name="login"><i class="fas fa-sign-in-alt ml-2"></i>تسجيل الدخول</button>
                                <label class="mt-3">لا تملك حساب؟ <a href="signup" class="text-light font-weight-bold">إنشاء حساب جديد</a></label>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php include 'footer.php' ?>
    </div>
    <script src="./js/popper.min.js"></script>
    <script src="./js/ajax.min.js"></script>
    <script src="./js/bootstrap.min.js"></script>
    <script src="./js/aos.js"></script>
    <script src="./js/main.js"></script>
    <script>
        AOS.init();
        $(function() {
            var headerHeight = $(".header").height(),
                footerHeight = $("footer").height(),
                windowHeight = $(window).height(),
                newHeight = windowHeight - (headerHeight + footerHeight) - 60;
            $("#loginContent").css("min-height", newHeight);

            // $("#login").click(() => {
            //     var email = $("#email").val(),
            //         pwd = $("#pwd").val();
            //     $.post('inc/run.php', {
            //         target: "login",
            //         email: email,
            //         pwd: pwd
            //     }, (data) => {
            //         var data = JSON.parse(data);
            //         console.log(data.error);
            //         if (data.error == true) {
            //             notiModal(`<i class="fas fa-times text-danger ml-2"></i> ${data.message}`);
            //         } else {
            //             window.location.href = data.url;
            //         }
            //     })
            // })
        })
    </script>
</body>

</html>
<?php
session_start();
require 'inc/func.php';

// تسجيل موقع الزائر (غير المسجل الدخول)
// هذا الكود سيعمل فقط إذا كان المستخدم غير مسجل الدخول
// وستعتمد دالة update_guest_location على الكوكيز والـ IP
if (empty($_SESSION['id'])) {
    $user_ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    // استدعاء الدالة لتحديث موقع الضيف.
    // دالة update_guest_location موجودة في location_updater.php الذي تم تضمينه عبر func.php
    update_guest_location($conn, $user_ip, $user_agent);
}

if(!empty($_SESSION['id'])){
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
    <link rel="stylesheet" href="./css/aos.css">
    <link rel="stylesheet" href="./css/main.min.css"> 
    </head>

<body>
                <input type="text" value="<?=$_SESSION['acc_type']?>" id="accType" hidden="">
                    <div class="modal fade" id="notiModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLongTitle">تنبيه</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">×</span>
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
                <div class="intro-header header">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-light">

                <a href="#" class="navbar-brand">
                    <img src="img/logo.webp" alt=' class=' img-fluid'>
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse"
                    data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ml-auto">
                        <li class='nav-item active'>
                            <a class='nav-link' href='index'>الرئيسية</a>
                        </li>
                        <li class='nav-item'>
                            <a class='nav-link' href='signup'>إنشاء حساب</a>
                        </li>
                        <li class='nav-item'>
                            <a class='nav-link' href='login'>تسجيل الدخول</a>
                        </li>
                        <li class='nav-item'>
                            <a class='nav-link' href='aboutus'>من نحن</a>
                        </li>
                        <li class='nav-item'>
                            <a class='nav-link' href='terms'>شروط الخدمة</a>
                        </li>
                        <li class='nav-item'>
                            <a class='nav-link' href='privacy'>إتفاقية الخصوصية</a>
                        </li>
                        <li class='nav-item'>
                            <a class='nav-link' href='index#contactUs'>تواصل معنا</a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </div>
    <div class="index-content">
        <section class="intro">
            <div class="intro-content">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6">
                            <h2>واسطيتكو - Wastetco</h2>
                            <p>
                                نقوم بدور الوساطة بين البائع والمشتري فيتم خصم قيمة الخدمة من المشتري ولا يتم تسليم المبلغ
                                للبائع
                                إلا بعد الإنتهاء من العمل، بالإضافة لوجود فريق دعم من الموقع لحل كافة الشكاوى
                                </ul>
                            </p>
                            <h3>تسجيل الدخول:</h3>
                            <?php
                            if (isset($_POST['login'])) {
                                $email = safe($_POST['email'], 'mail');
                                $pwd = safe($_POST['pwd'], 'string');
                                // تم التعديل هنا: التحقق من وجود $_POST['remember']
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
                            <form role="form" method="post">
                                <div class="form-group">
                                    <label for="email">البريد الإلكتروني:</label>
                                    <input type="email" name="email" class="form-control"
                                        placeholder="إدخل البريد الإلكتروني هنا">
                                </div>
                                <div class="form-group">
                                    <label for="password">كلمة السر:</label>
                                    <input type="password" name="pwd" class="form-control" placeholder="إدخل كلمة السر هنا">
                                    <input type="checkbox" name="remember" value="remember" class="ml-2 mt-3"><label
                                        for="remeber">تذكرني</label>
                                </div>
                                <div class="form-group">
                                    <button class="btn btn-info" type="submit" name="login">تسجيل الدخول</button><br>
                                    <label class="mt-2">لا تملك حساب؟ <a href="signup"
                                            class="text-light font-weight-bold">إنشاء حساب جديد</a></label>
                                </div>
                            </form>
                            </div>
                        <div class="col-md-6">
                            <img class="img-fluid" style="margin-top: 3.5rem;" src="img/money.webp" alt="Money photo">
                        </div>
                    </div>
                </div>
            </div>
    
        </section>
        <section class="why mt-5" data-aos="fade">
            <div class="container">
                <div class="row">
                    <div class="col-sm-6">
                        <h2 class="text-center mb-4">ليه فلوسك أمان معانا؟</h2>
                        <p class="lead">
                            لو أنت بائع:
                        <ul class="mt-3">
                            <li>
                                المشتري بيتخصم من رصيده قيمة العمل بمجرد ما يطلب العمل
                            </li>
                            <li>
                                المشتري ميقدرش يلغي العمل ما دام فترة التفيذ المحددة منتهتش
                            </li>
                            <li>
                                بمجرد التسليم بيتضاف قيمة العمل لرصيدك
                            </li>
                            <li>لو المشتري موافقش على الإستلام فريق الدعم بيراجع الأوردر وبيضمن لصاحب الحق فلوسه</li>
                        </ul>
                        </p>
                        <p class="lead mt-4">
                            لو أنت مشتري:
                        <ul class="mt-3">
                            <li>البائع مبيستلمش الفلوس إلا بعد موافقتك على إستلام العمل المقدم</li>
                            <li>
                                يمكنك إلغاء طلب العمل وإسترداد أموالك إن لم يقم البائع بالتسليم قبل الموعد المحدد
                            </li>
                            <li>في حالة عدم تسليم البائع للعمل بالشكل المتفق عليه يمكنك تقديم شكوى عليها لا يستلم البائع
                                أمواله ويطالب بإكمال العمل وفي حالة عدم الإكمال بالشكل المطلوب تسترد أنت اموالك</li>
                        </ul>
                        </p>
                    </div>
                    <div class="col-sm-6">
                        <img src="img/safe.webp" alt="Safe Image" class="img-fluid">
                        </div>
                </div>
            </div>
        </section>
        <section class="aboutus mt-4 text-center" id="aboutUs" data-aos="flip-left">
            <div class="container">
                <h2>من نحن؟</h2>
                <p>موقع واسطيتكو هو موقع خدمي وسيط بين البائع والمشترى يعمل على ضمان حقوق الطرفين فى جميع المعاملات المالية
                    الالكترونية الكبيره والصغيره</p>

                <div class="container">
                    <div class="embed-responsive embed-responsive-16by9">
                        <iframe class="embed-responsive-item rounded"
                                src="https://www.youtube.com/embed/b-Tq1OpkXOc?controls=0&showinfo=0&rel=0&modestbranding=1"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen>
                        </iframe>
                    </div>
                </div>
                    <br><br>
                <a href="aboutus" class="btn btn-info rounded-pill btn-lg">
                    إعرف عنا أكثر
                </a>
            </div>
        </section>
        <section class="terms mt-4" id="terms" data-aos="zoom-in">
            <div class="container">
                <div class="row">
                    <div class="col-sm-6">
                        <h2>إتفاقية الإستخدام والخصوصية</h2>
                        <p class="lead">تتغير شروط الاستخدام من حين لآخر، وفي حالة إجراء مثل هذه التغييرات، سيعرض الموقع
                            إخطارات واضحة لكي يكون المُستخدِم على علم بها. استمرار المُستخدِم في استعمال الموقع الإلكتروني
                            واسطيتكو.كوم قائم على قبول شروط وأحكام الاستخدام هذه، حسب ما يجري تعديلها من وقت لآخر من أجل
                            معاملة أفضل و جودة أعلى. أي مخالفة لهذه الاتفاقية من قبل العميل / المشترك ستعرض حسابه للإيقاف أو
                            الحذف بدون إشعار مسبق ودون استرجاع أي مبالغ في بعض الحالات الخاصة .
                        </p>
                        <a href="terms" class="btn btn-info btn-lg rounded-pill">قراءة المزيد</a>
                    </div>
                    <div class="col-sm-6">
                        <img src="img/terms.webp" alt="Terms & Conditions image" class="img-fluid">
                    </div>
                </div>
            </div>
        </section>
        <section class="payments mt-4 text-center" id="payments">
            <div class="container">
                <h2>وسائل الدفع</h2>
                <p>يمكنك إضافة وسحب أرصدتك من خلال وسائل الدفع المذكورة أدناه</p>
                <div class="row mt-4">
                    <div class="col-sm-6">
                        <div class="payment-method mb-3" data-aos="flip-left">
                            <img src="img/vodafone.webp" alt="Vodafone icon" class="mb-3">
                            <h5 class="font-weight-bold">فودافون كاش</h5>
                            <p>من خلال رقم: 01090068651</p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="payment-method mb-3" data-aos="flip-right">
                            <img src="img/bank.webp" alt="Etisalat icon" class="img-fluid mb-3">
                            <h5 class="font-weight-bold">البنك الأهلي المصري</h5>
                            <p>على حساب: 5363010798070001011</p>
                        </div>
                    </div>
                    <div class="col-sm-12" data-aos="fade-down">
                        <a href="payment" class="btn btn-lg btn-info grad-btn rounded-pill">أعرف أكثر</a>
                    </div>
                </div>
            </div>
        </section>
        <section class="contactus mt-4" id="contactUs">
            <div class="container">
                <h2 class="text-center mb-4">تواصل معنا</h2>
                <div class="row">
                    <div class="col-sm-6">
                        <h3>معلومات الإتصال</h3>
                        <p class="lead">
                            نحرص دائما على تقديم أفضل خدمة لكم، في حالة وجود أي إستفسارات أو إقتراحات أو قد واجهت مشكلة، لا
                            تتردد أبدا في التواصل معنا.
                            <table class="table table-borderless text-light">
                                <tr>
                                    <th>
                                        <i class="fas fa-phone ml-2"></i>رقم الهاتف:    
                                    </th>
                                    <td>
                                        01099029162
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <i class="fas fa-envelope ml-2"></i>البريد الإلكتروني: 
                                    </th>
                                    <td>
                                        admin@wastetco.com
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <i class="fas fa-map-marked-alt ml-2"></i>مقر الشركة:
                                    </th>
                                    <td>
                                        مقر الشركة: مصر - محافظة أسيوط - مركز ديروط - قرية صنبو - شارع الجمهورية - عقار 27
                                    </td>
                                </tr>
                            </table>
                        </p>
                    </div>
                    <div class="col-sm-6">
                        <h3>
                            اترك رسالتك
                        </h3>
                        <p>يمكنك إرسال رسالة لنا من خلال ملئ النموذج التالي:</p>
                        <form role="form">
                            <div class="form-group">
                                <label for="name">الإسم بالكامل:</label>
                                <input type="text" id="name" class="form-control" placeholder="إدخل إسمك هنا بالكامل">
                            </div>
                            <div class="form-group">
                                <label for="email">البريد الإلكتروني</label>
                                <input type="email" id="email" placeholder="axxxx@gmail.com" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="phone">رقم الهاتف:</label>
                                <input type="text" id="phone" placeholder="010xxxxxxxx" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="title">عنوان الرسالة:</label>
                                <input type="text" id="title" placeholder="اكتب هنا عنوان قصير يعبر عن محتوى الرسالة"
                                    class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="message">رسالتك:</label>
                                <textarea class="form-control" id="message"
                                    placeholder="....أكتب كل ما تريد إخبارنا به هنا"></textarea>
                            </div>
                            <div class="form-group">
                                <button type="button" id="sendMessage" class="btn btn-info btn-block">إرسال الرسالة</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
        <section class="sign mt-4 text-center" data-aos="flip-left">
            <div class="container">
                <h2>جاهز إنك تبدأ؟</h2>
                <p class="lead mt-3">متفوتش الفرصة وإضمن كل معاملاتك المالية على الإنترنت من خلالنا</p>
                <div class="text-center mt-4">
                    <a href="signup" class="btn btn-outline-info ml-2">إنشاء حساب</a>
                    <a href="login" class="btn btn-outline-dark mr-2">تسجيل الدخول</a>
                </div>
            </div>
        </section>
        <div id="fb-root"></div>
        <script>
        window.fbAsyncInit = function() {
            FB.init({
                xfbml: true,
                version: 'v8.0'
            });
        };
    
        (function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s);
            js.id = id;
            js.src = 'https://connect.facebook.net/ar_AR/sdk/xfbml.customerchat.js';
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
        </script>
    
        <div class="fb-customerchat" attribution=setup_tool page_id="108167971020615" theme_color="#000000"
            logged_in_greeting="مرحباً فريق " واسطيتكو" جاهز للرد عليك , كيف يمكننا مساعدتك ؟ "
      logged_out_greeting=" مرحباً فريق "واسطيتكو" جاهز للرد عليك , كيف يمكننا مساعدتك ؟ ">
          </div>
        <?php include 'footer.php' ?>
    </div>
    <script src=" ./js/popper.min.js">
        </script>
        <script src="./js/ajax.min.js">
        </script>
        <script src="./js/bootstrap.min.js"></script>
        <script src="./js/aos.js"></script>
        <script src="./js/main.js"></script>
        <script>
        AOS.init();
        $(function() {
            $("#sendMessage").click(() => {
                var name = $("#name").val(),
                    email = $("#email").val(),
                    phone = $("#phone").val(),
                    title = $("#title").val(),
                    message = $("#message").val();
                $.post('inc/run.php', {
                    target: 'new-inbox-message',
                    name: name,
                    email: email,
                    phone: phone,
                    title: title,
                    message: message
                }, (data) => {
                    var data = JSON.parse(data);
                    if (data.error) {
                        notiModal(
                            `<i class="fas fa-times text-danger ml-2"></i> ${data.message}`);
                    } else {
                        notiModal(
                            `<i class="fas fa-check text-success ml-2"></i> ${data.message}`
                            );
                    }
                })
            })
        })
        </script>
</body>

</html>
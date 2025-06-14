<?php
session_start();
include '../inc/func.php';
remember();
if (!$_SESSION['id']) {
    header("Location: ../login");
} else {
    $balance = balance($conn, $_SESSION['id']);
    $available = $balance['available'];
    $banned = $balance['banned'];
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <?php include 'head.php'?>

    </head>

    <body>
        
        
        <?php include 'header.php' ?>
        <div class="account-page" id="main">
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
                <!-- End Noti Modal -->
                <section class="balance-summary text-center rounded">
                    <div class="container">
                        <div class="row">
                            <div class="col-sm-6 col-md-4 balance-area">
                                <h6>الرصيد المتاح</h6>
                                <h5><span id="availableBalance"><?php echo $available ?></span> جنيه مصري</h5>
                                <div class="container-fluid">
                                    <div class="row mt-3 mb-1">
                                        <div class="col-sm-6 mb-2">
                                            <a href="/pages/paymob" class="btn btn-sm btn-success" style="color: #fff" type="button" ><i class="fas fa-plus ml-1"></i>إضافة
                                                رصيد</a>
                                        </div>
                                        <div class="col-sm-6 mb-2">
                                            <button class="btn btn-sm btn-success" type="button" id="addProof"><i class="far fa-check-square ml-1"></i>اثبات الدفع</button>
                                        </div>										
                                        <div class="col-sm-6 mb-2">
                                            <button class="btn btn-sm btn-danger" type="button" id="withdrawModal" limit="<?php echo $available ?>"> <i class="fas fa-hand-holding-usd ml-1"></i>سحب رصيد</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4 balance-area">
                                <h6>الرصيد المرهون لدى الموقع</h6>
                                <h5><span id="bannedBalance"><?php echo $banned ?></span> جنيه مصري</h5>
                            </div>
                            <div class="col-md-4 balance-area" style="border: none;">
                                <h6>إجمالي الرصيد</h6>
                                <h5><span class="total-balance"><?php echo $available + $banned ?></span> جنيه مصري</h5>
                            </div>
                        </div>
                    </div>
                </section>
                <div class="container">
                    <?php
                    if (isset($_POST['request_add'])) {
                        $receipt = $_FILES['receipt'];
                        $upload  = new_receipt($receipt);
                        if ($upload['error']) {
                            sendError($upload['message']);
                        } else {
                            $add = add_request($conn, $upload['name']);
                            if ($add['error']) {
                                sendError($add['message']);
                            } else {
                                sendSucc("تم إرسال طلب إضافة الرصيد الخاص بك بنجاح");
                            }
                        }
                    }
                    ?>
                    <div class="row">
                        <div class="col-md-8">
                            <ul class="nav nav-tabs mt-4">
                                <li class="nav-item">
                                    <a class="nav-link content-switch active" href="#" id="btn-profile">تحديث معلوماتي</a>
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
                                        $user = is_user($conn, $_SESSION['id']);
                                        $user = $user['user'];
                                        if ($user['verified'] == "yes") {
                                        ?>
                                            <p class="lead font-weight-bold mb-3 mt-3"><i class="fas fa-check-cicle ml-2 text-success"></i> أنت عميل موثوق</p>
                                        <?php
                                        }
                                        ?>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label>الإسم الأول</label>
                                                    <input type="text" class="form-control" id="firstname" disabled>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label>الإسم الأخير</label>
                                                    <input type="text" class="form-control" id="lastname" disabled>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label>الرقم القومي</label>
                                                    <input type="text" class="form-control" id="nid" disabled>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label>البريد الإلكتروني</label>
                                                    <input type="text" class="form-control" id="email" disabled>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label>رقم الهاتف</label>
                                                    <input type="text" class="form-control" id="phone">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <button class="btn btn-success" type="button" id="updatePhone" style="margin-top:32px;"><i class="fas fa-check ml-2"></i>تحديث
                                                        البيانات</button>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label>كلمة السر الجديدة</label>
                                                    <input type="password" id="pwd" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <button class="btn btn-success" user="<?=$_SESSION['id']?>" type="button" id="updatePwd" style="margin-top:32px;">تحديث كلمة السر</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                                <section class="money" id="money">
                                    <h4 class="mt-3 mb-2">المعاملات المالية</h4>
                                    <div class="container">
                                        <table class="table table-borderless text-center table-responsive">
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
                                            $finance_history = finance_process($conn, $_SESSION['id'], 'all');
                                            if ($finance_history['error']) {
                                                sendError($finance_history['message']);
                                            } else {
                                                if ($finance_history['check'] == 0) {
                                                    emptyRow(5, 'لا يوجد أي معاملات مالية لهذا الحساب حتى الان');
                                                } else {
                                                    foreach ($finance_history['process'] as $finance_process) {
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
                            <?php include 'sidebar.php'; ?>
                        </div>
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
                $("#money").hide();
                var vodafone = "01016421977";
                $.post('../inc/run.php', {
                    target: "get-profile-data"
                }, (data) => {
                    var data = JSON.parse(data);
                    if (data.error) {
                        notiModal(`<i class="fas fa-times text-danger ml-2"></i>${data.message}`);
                    } else {
                        $("#firstname").val(data.firstname);
                        $("#lastname").val(data.lastname);
                        $("#nid").val(data.nid);
                        $("#email").val(data.email);
                        $("#phone").val(data.phone);
                    }
                })

                $("#updatePhone").click(() => {
                    var phone = $("#phone").val();
                    if (!phone) {
                        notiModal(`<i class="fas fa-times text-danger ml-2"></i>يجب إدخال رقم هاتف`);
                    } else {
                        $.post("../inc/run", {
                            target: "update-phone",
                            phone: phone
                        }, (data) => {
                            var data = JSON.parse(data);
                            if (data.error) {
                                notiModal(
                                    `<i class="fas fa-times text-danger ml-2"></i>${data.message}`);
                            } else {
                                notiModal(
                                    `<i class="fas fa-check text-success ml-2"></i>تم تحديث بياناتك بنجاح`
                                );
                            }
                        })
                    }
                })
                
                $("#addModal").click(() => {
                    notiModal(`<h4>ايداع</h4>
                    <p class="text-secondary">
                        لايداع رصيد الى حسابك يمكنك اختيار طريقه الدفع المفضله لديك ثم قم باثبات الدفع						
                    </p>
<!--					
                    <div class="text-secondary">

                                    <div class="row mt-3 text-center">
                                            <div class="col-md">
                                                <a href="./paymob" class="fab fa-cc-paypal fa-5x"></a>
                                            </div>
                                            <div class="col-md">
                                                <a href="./payment"  class="fas fa-wallet fa-5x"> </a>
                                            </div>
                                            <div class="col-md">
                                                <a href="https://portal.myfatoorah.com/EGY/la/0505119721202350"  class="fas fa-money-bill-alt fa-5x"> </a>
                                     </div>						
                    </div>`);
-->
                    $("#requestAdd").click(function() {
                        var code = $("#code").val();
                        if (!code) {
                            $("#notiModal").modal('hide');
                            notiModal(
                                `<i class="fas fa-times text-danger ml-1"></i>يجب إدخال كود عملية التحويل`
                            );
                        } else {
                            $.post("../inc/run", {
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
				$("#addProof").click(() => {
                    notiModal(`<h4>اثبات الدفع</h4>
                    <p class="text-secondary">
                        اذا لم يتم اضافه الرصيد تلقائياً الى حسابك قم بارفاق ايصال او فاتورة الدفع التى قمت بدفعها من قبل						
                    </p>
                  			
                    </p>					
                    <form method="post" enctype="multipart/form-data">
                        <div class="container">
                            <div class="row">
                                <div class="col-8">
                                    <div class="form-group">
                                        <label for="code">إيصال الدفع:</label>
                                        <input type="file" class="form-control" name="receipt">
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <button class="btn btn-sm btn-block btn-success" type="submit" name="request_add" style="margin-top: 35px;"><i class="fas fa-check ml-1"></i>تأكيد الطلب</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>`);

                    $("#requestAdd").click(function() {
                        var code = $("#code").val();
                        if (!code) {
                            $("#notiModal").modal('hide');
                            notiModal(
                                `<i class="fas fa-times text-danger ml-1"></i>يجب إدخال كود عملية التحويل`
                            );
                        } else {
                            $.post("../inc/run", {
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
                        يمكنك سحب أرصدتك في أي وقت من خلال إدخال قيمة المبلغ المراد سحبه في الحقل أدناه بشرط أن يكون المبلغ متوفر في خانة رصيدك المتاح وسيتم تحويل المبلغ إليكم وخصمه من حسابكم خلال مدة أقصاها 6 ساعات
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
                            $.post('../inc/run', {
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
                            notiModal(`<i class="fas fa-check text-success ml-2"></i> ${data.message}`);
                            $.post("../inc/run.php", { target: "logout" }, () => {
                                window.location.href = "../login.php";
                            });
                        }
                    })
                })
            })

        </script>
    </body>

    </html>
<?php
}

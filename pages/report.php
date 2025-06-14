<?php
session_start();
include '../inc/func.php';
remember();
if(!$_SESSION['id']){
    header("Location: ../login");
}else{
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
        <div class="order-page">
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
                <!-- End Noti Modal -->
                <div class="modal fade" id="refuseModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLongTitle">رفض تسليم طلب</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body" id="refuseModal">
                                <div class="conainer-fluid">
                                    <form role="form">
                                        <div class="form-group">
                                            <label class="font-weight-bold">سبب الرفض:</label>
                                            <textarea id="refuseReason" style="height: 50px;" class="form-control" placeholder="يرجى شرح كافة الأسباب التي دفعتك لرفض إستلام الطلب"></textarea>
                                        </div>
                                        <div class="form-group mt-3">
                                            <button type="button" class="btn btn-block btn-info grad-btn" id="confirmRefuse">تأكيد عدم الإستلام</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="container mt-3">
                                <?php
                                $report = safe($_GET['id'], 'int');
                                if (!$report) {
                                    sendError("هذه الصفحة غير موجودة يرجى التأكد من الرابط");
                                } else {
                                    $report = report($conn, $report);
                                    if ($report['error']) {
                                        sendError($report['message']);
                                    } else {
                                        $report = $report['report'];
                                        $user = is_user($conn, $report['user']);
                                        if($_SESSION['id'] != $report['user'] && $_SESSION['acc_type'] != "admin"){
                                            sendError("ليس لديك صلاحيات عرض هذه الصفحة");
                                        }else{
                                            $messages = messages($conn, $report['id'], 'report');
                                            if ($messages['error']) {
                                                sendError($messages['message']);
                                            } else {
                                    ?>
                                                <div class="report">
                                                    <section class="summary">
                                                        <table class="table table-borderless table-responsive">
                                                            <thead>
                                                                <tr>
                                                                    <th colspan="5"><?= $report['title'] ?></th>
                                                                </tr>
                                                                <tr>
                                                                    <td>العميل: <br>
                                                                        <?= short_name($user['user']); ?></td>
                                                                    <td>كود الشكوى <br>
                                                                        #<?= $report['id'] ?></td>
                                                                    <td>تاريخ البدأ: <br>
                                                                        <?= arabicDate($report['report_timestamp']) ?></td>
                                                                    <td>الحالة: <br><?= visible_report_status($report['report_status']) ?>
                                                                    </td>
                                                                </tr>
                                                            </thead>
                                                        </table>
                                                    </section>
                                                    <section class="details">
                                                        <h5>التفاصيل:</h5>
                                                        <p>
                                                            <?= nl2br($report['description']) ?>
                                                        </p>
                                                    </section>
                                                    <section class="messages">
                                                        <h5 class="mb-3">الرسائل:</h5>
                                                        <?php
                                                        if ($messages['num'] == 0) {
                                                        ?>
                                                            <p class="lead font-weight-bold text-center">لا يوجد رسائل في هذه الشكوى حتى الآن
                                                            </p>
                                                        <?php
                                                        } else {
                                                            foreach ($messages['messages'] as $message) {
                                                                echo message_item($conn, $message);
                                                            }
                                                        }
                                                        ?>
                                                    </section>
                                                    <section class="new-message-area">
                                                        <form role="form" class="mt-3">
                                                            <div class="form-group">
                                                                <label for="newMessage">الرسالة:</label>
                                                                <textarea id="newMessage" class="form-control" placeholder="إكتب كل ما تود أن تكتبه في الرسالة هنا ثم إضغط زر 'إرسال' أدناه"></textarea>
                                                                <input type="text" id="reportId" value="<?= $report['id'] ?>" hidden>
                                                            </div>
                                                            <div class="form-group">
                                                                <button type="button" id="sendMessage" class="btn btn-info grad-btn">
                                                                    إرسال الرسالة
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </section>
                                                    <?php
                                                    if ($_SESSION['acc_type'] == "admin" && $report['report_status'] == "running") {
                                                    ?>
                                                        <section class="actions">
                                                            <div class="container-fluid">
                                                                <button type="button" class="btn btn-sm btn-danger" id="closeReport" report="<?= $report['id'] ?>">
                                                                    إغلاق الشكوى
                                                                </button>
                                                            </div>
                                                        </section>
                                                    <?php
                                                    }
                                                    ?>
                                                </div>
                                    <?php
                                            }
                                        }
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <?php include 'sidebar.php'; ?>
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
        <script src="../js/jquery.scrollTo.min.js"></script>
        <script>
            AOS.init();
            $(function() {
                $(".messages-item").each(function(i){
                    var item = $(this),
                    height = item.find('.message').height() + 50;
                    item.height(height)
                })
                $("#closeReport").click(function() {
                    var reportId = $("#reportId").val(),
                        $this = $(this);
                    if (!reportId) {
                        notiModal(
                            `<i class="fas fa-times text-danger ml-2"></i> خطأ!! يرجى إعادة تحميل الصفحة والمحاولة مجدداً`
                        );
                    } else {
                        if (!$.isNumeric(reportId)) {
                            notiModal(
                                `<i class="fas fa-times text-danger ml-2"></i> خطأ!! يرجى إعادة تحميل الصفحة والمحاولة مجدداً`
                            );
                        } else {
                            $.post('../inc/run.php', {
                                target: "action-report",
                                report: reportId,
                                action: 'closed'
                            }, (data) => {
                                var data = JSON.parse(data);
                                if (data.error) {
                                    notiModal(
                                        `<i class="fas fa-times text-danger ml-2"></i> ${data.message}`
                                    );
    
                                } else {
                                    notiModal(
                                        `<i class="fas fa-check text-success ml-2"></i> تم إغلاق الشكوى بنجاح`
                                    );
                                    $this.remove();
                                }
                            })
                        }
                    }
                })
                $("#sendMessage").click(function() {
                    var message = $("#newMessage").val();
                    var reportId = $("#reportId").val();
                    if (!message) {
                        notiModal(`<i class="fas fa-times text-danger ml-2"></i> لا يمكن إرسال رسالة فارغة`);
                    } else {
                        if (!reportId) {
                            notiModal(
                                `<i class="fas fa-times text-danger ml-2"></i> خطأ!! يرجى إعادة تحميل الصفحة والمحاولة مجدداً`
                            );
                        } else {
                            if (!$.isNumeric(reportId)) {
                                notiModal(
                                    `<i class="fas fa-times text-danger ml-2"></i> خطأ!! يرجى إعادة تحميل الصفحة والمحاولة مجدداً`
                                );
                            } else {
                                $.post('../inc/run.php', {
                                    target: "new-message",
                                    type: "report",
                                    for_id: reportId,
                                    new_message: message
                                }, (data) => {
                                    var data = JSON.parse(data);
                                    if (data.error) {
                                        notiModal(
                                            `<i class="fas fa-times text-danger ml-2"></i> ${data.message}`
                                        );
                                    } else {
                                        $(".messages").find(".newst-message").removeClass("newst-message");
                                        var item = `<table class="newst-message table table-responsive table-borderless message-table text-light p-2 mb-3 mess-sender">
                                                <tr>
                                                    <th class="text-center">
                                                        ${data.sender_name} <br>
                                                        [${data.sender}]
                                                    </th>
                                                    <td class="message-text">
                                                    ${data.mess}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-center" colspan="2">
                                                        <i class="fas fa-calendar-alt ml-1"></i> : ${data.date}
                                                        <i class="fas fa-clock ml-1"></i> : ${data.time}
                                                    </td>
                                                </tr>
                                            </table>`;
                                        //  var item = `<div class="messages-item">
                                        //             <div class="container">
                                        //                 <div class="user-area text-center">
                                        //                     <span class="fa-stack fa-2x d-block mb-2">
                                        //                         <i class="fas fa-circle fa-stack-2x"></i>
                                        //                         <i class="fas fa-user fa-stack fa-1x fa-inverse"></i>
                                        //                     </span>
                                        //                     ${data.sender_name} <br>
                                        //                     (${data.sender})
                                        //                 </div>
                                        //                 <div class="message-area">
                                        //                     <p class="message">
                                        //                         ${data.mess}
                                        //                     </p>
                                        //                     <span class="message-details text-dark">
                                        //                         <i class="fas fa-calendar-alt ml-1"></i>
                                        //                         التاريخ:
                                        //                         ${data.date}
                                        //                         <i class="fas fa-clock ml-1"></i>
                                        //                         الوقت:
                                        //                         ${data.time}
                                        //                     </span>
                                        //                     <div class="clearfix"></div>
                                        //                 </div>
                                        //             </div>
                                        //         </div>`;
                                        $(".messages").append(item);
                                        $("#newMessage").val("");
                                        $('.messages').scrollTo('.newst-message');

                                    }
                                })
                            }
                        }
                    }
                })
            })
        </script>
    </body>
    
    </html>
    <?php
}
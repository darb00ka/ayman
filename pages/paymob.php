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
        <div class="account-page">
            <?php include 'header.php' ?>
            <div class="container">
                <!-- Noti Modal -->
                <input type="text" value="<?=$_SESSION['acc_type']?>" id="accType" hidden="">
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
                <div class="container">
                    <div class="row">
                        <div class="col-md-8">
                            <section class="page-content">
                                <section class="profile p-5" id="profile">
                                    <div class="container">

                                        <div class="row">
                                                <div class="col-8">
                                                    <form action="/pages/payment" method="post" >
                                                          <div class="form-group">
                                                                <label for="payment-type">اختر طريقه الدفع </label>
                                                                    <select name="type" class="form-control" id="payment-type">
                                                                      <option value="mobile_wallet" >المحفظه الالكترونيه(ڤودافون,اورنچ,اتصالات)</option>
                                                                      <option  value="online_card">البطاقة الائتمانية (فيزا , ماستر كارد , ميزه</option>
                                                                      <option value="kiosk" >الدفع فى المتجر (امان , مصارى , ممكن , سداد)</option>

                                                                    </select>
                                                            </div>
                                                            <div class="form-group">
                                                                    <label for="payment-value">ادخل المبلغ </label>
                                                                    <input type="number" name="value" class="form-control" id="payment-value" placeholder="" value="10" required>
                                                            </div>
                                                             <div class="form-group">
                                                                    <label for="payment-phone">ادخل رقم الهاتف  </label>
                                                                    <input type="text" name="phone" class="form-control" id="payment-phone" placeholder="">
                                                            </div>
                                                             <button type="submit" style = "margin: auto;display: block" class="btn btn-success" >تأكيد</button>

                                                        
                                                    </form>
                                                    
                                                    
                                                </div>
                                                
                                        </div>

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
        
        <script src="../js/popper.min.js"></script>
        <script src="../js/ajax.min.js"></script>
        <script src="../js/bootstrap.min.js"></script>
        <script src="../js/aos.js"></script>
        <script src="../js/main.js"></script>
        <script src="../js/notifications.js"></script>
        <script>
            AOS.init();
            $(function() {
                if($("#accType").val() == "admin"){
                    if($(window).width() < 0){   
                        window.location.replace("https://wastetco.com");
                    }
                }
            })
        </script>
    </body>

    </html>
    <?php
}
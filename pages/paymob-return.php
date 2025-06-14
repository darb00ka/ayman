<?php
session_start();
include '../inc/func.php';
remember();
function encrypt_decrypt($string, $action = 'enc')
{
    $encrypt_method = "AES-256-CBC";
    $secret_key = '7sdfsd4545ewfw7865hfcr'; // user define private key
    $secret_iv = 'fdsfsdf654644sfsdf'; // user define secret key
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16); // sha256 is hash_hmac_algo
    if ($action == 'enc') {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    } else if ($action == 'dec') {
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }
    return $output;
}


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
                                                <div class="col-12">
                                                        <?php 
                                                        
                                                            $message = encrypt_decrypt( $_GET["message"] , "dec") ?? "حصل خطأ غير معروف ";
                                                            $error = encrypt_decrypt($_GET["error"] , "dec") ?? 0 ;
                                                            $button = encrypt_decrypt($_GET["button"] , "dec") ?? null ;
                                                                
                                                        ?>
                                                        <div class="text-center wy-3 <?= ($error) ? "text-danger" : ""  ?> "> <?= $message ?> </div>
                                                        <?php if ($button != null ) { ?>
                                                            <a style="width: fit-content;margin-right: auto !important;margin-left: auto !important;" class="btn btn-success d-block mt-4 mb-4 btn-lg  " href="<?= encrypt_decrypt($_GET["url"]  , "dec") ?>" role="button"><?= $button ?></a>
                                                        <?php } ?>
                                                        
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
        <?php  include '../footer0.php' ?>
        <script src="../js/popper.min.js"></script>
        <script src="../js/ajax.min.js"></script>
        <script src="../js/bootstrap.min.js"></script>
        <script src="../js/aos.js"></script>
        <script src="../js/main.js?mode=finish"></script>
        <script src="../js/notifications.js"></script>

    </body>

    </html>
    <?php
}
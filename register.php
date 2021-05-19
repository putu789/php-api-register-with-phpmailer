<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/OAuth.php';
require 'phpmailer/src/POP3.php';
require 'phpmailer/src/SMTP.php';
header("Content-Type: application/json");
include 'yourconnection.php'; //connection database mysql file php//koneksi ke database
$konten = file_get_contents("php://input");
$decode = json_decode($konten, true);
$response = array();
 
 
if (isset($decode['user_email'])) {
	$userNama = $decode['user_name']; // ambil nilai nama dari form aplikasimu
	$userEmail = $decode['user_email']; //ambil nilai email dari form aplikasimu
	$userAlamat = $decode['user_address'];
	$userPassword = md5($decode['user_password']);
	$userTelpon = $decode['user_telepon'];

	
	$chekmail = num_rows(query("SELECT email FROM user_mobile WHERE email = '$userEmail' "));
	if ($chekmail > 0 ) {
		$response = array(
			'status' => 'ada' //cek email apakah sudah terpakai
		);
	}else{
		//jika email belum terpakai maka akan memasukan data
		$token = hash('sha256', md5($userEmail));
		$check = query("INSERT INTO user_mobile (email,password,nama,alamat,status,token,nomor_tlfn) VALUES ('$userEmail','$userPassword','$userNama','$userAlamat','0','$token','$userTelpon')");
		if(isset($check)){
			
			$mail = new PHPMailer(true);
			$mail->SMTPDebug = 0;
			$mail->isSMTP();
			$mail->Host = 'urlhostemailmu.com';
			$mail->SMTPAuth = true;
			$mail->Username = 'emailmu@email.com';
			$mail->Password = 'passwordemailmu';
			$mail->SMTPSecure = 'ssl';
			$mail->Port = 465;
			$mail->setFrom('emailmu@email.com', 'Nama Mu');
			$mail->addAddress($userEmail, $userNama);
			$mail->isHTML(true);
			$mail->Subject = "Aktivasi Pendaftaran Pengguna Sukses ";
			$mail->Body = "Hai ".$userNama.",<br> 
			<p>Selamat, anda berhasil membuat akun. Untuk mengaktifkan akun anda silahkan klik link Verivikasi Berikut ini.
			 <a href='http://urlmu.com/activation.php?mailfrom=".$userEmail."&active=".$token."'>Verivikasi</a></p>";
			$mail->send();

			$response = array(
				'status' => 'success',
				 'code' => 20
			  );
		}else{
			 
				$response = array(
				'status' => 'Gagal',
				 'code' => 500
			);
	 
	 	}
	}
}else{
	$response = array(
    'metadata' => array(
    'status' => "Data User Kosong",
    'code' => 404
	)
  );
} 
 echo json_encode($response);
?>
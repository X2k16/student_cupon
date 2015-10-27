<?php
include_once(dirname(__FILE__) . "/../config.php");

function response($response_code, $data) {
	http_response_code($response_code);
	header('Content-Type: application/json');
	$body = json_encode($data);
	echo $body;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
	response(405, array("result"=>"error", "message"=>"method not allowed"));
	exit(0);
}

try{
	$pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);

	// sql実行時のエラーをexceptionでとるようにする
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	// 重複チェック
	$stmt = $pdo->prepare("SELECT COUNT(id) AS count FROM users WHERE email = :mail");
	$stmt->bindValue(':mail', $_POST['mail'], PDO::PARAM_STR);
	$stmt->execute();
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	if ($result["count"] > 0) {
		response(422, array("result"=>"error", "message"=>"duplicated"));
		exit(0);
	}

	// 登録
	$stmt = $pdo->prepare("INSERT INTO users (name, sex, email, university, department, career) VALUES (:name, :sex, :mail, :university, :department, :career)");

	$stmt->bindValue(':name', $_POST['name'], PDO::PARAM_STR);
	$stmt->bindValue(':sex', $_POST['sex'], PDO::PARAM_INT);
	$stmt->bindValue(':mail', $_POST['mail'], PDO::PARAM_STR);
	$stmt->bindValue(':university', $_POST['university'], PDO::PARAM_STR);
	$stmt->bindValue(':department', $_POST['department'], PDO::PARAM_STR);
	$stmt->bindValue(':career', $_POST['career'], PDO::PARAM_INT);

	$stmt->execute();

	// 成功
	response(200, array("result"=>"success"));

}catch (PDOException $e){
	response(500, array("result"=>"error", "message"=>"database exception"));
}

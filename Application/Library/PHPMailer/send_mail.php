<?php
include ('./class.phpmailer.php');
$config = array(
        'host'=>'smtp.163.com',
        'port'=>'25',
        'user'=>'shaaoboo@163.com',
        'passwd'=>'19900314',
        'from'=>'shaaoboo@163.com',
        'fromname'=>'�ٲ�',
        
        );
$subject = 'this is a test mail';
$body = '<table style="background:#dfdfdf"><tr><td>��������</td></tr><tr><td>��������</td></tr></table>';
$address='763300134@qq.com';
$username='����';

$mail = new PHPMailer();
$mail->CharSet = 'gb2312';// �����ʼ����ַ�����
$mail->IsSMTP();// ʹ��SMTP��ʽ����
$mail->Host = $config['host'];// ������ҵ�ʾ�����
$mail->Port = $config['port'];

$mail->From = $config['from'];
$mail->FromName = $config['fromname'];
$mail->SMTPAuth = true;// ����SMTP��֤����

$mail->Username = $config['user'];
$mail->Password = $config['passwd'];
//$mail->AddAttachment("2012.jpg"); // ��Ӹ���
$mail->Subject=$subject;//�ʼ�����
$mail->AltBody="text/html";//������Ϣ������ʡ��
//$mail->IsHTML(true); // set email format to HTML //�Ƿ�ʹ��HTML��ʽ
$mail->MsgHTML($body);


$mail->AddAddress($address,$username);

if(!$mail->Send())
{
    echo "Mail Error :".$mail->ErrorInfo;
}else
{
    echo "��ϲ���ͳɹ���";
}

<?php

// ===========
// 事前の設定
// ===========

ini_set('log_errors', 'on');
ini_set('error_log', 'php.log');
error_reporting(E_ALL);

session_start();

$debug_flg = true;
function debug($str)
{
  global $debug_flg;
  if (!empty($debug_flg)) {
    error_log('デバッグ：' . $str);
  }
}

// =============
// 下のメッセージ部分
// =============
interface MessageInterface
{
  public static function Set($str);
  public static function Clear();
}

class Message implements MessageInterface
{
  // メッセージをセット
  public static function Set($str)
  {
    if (empty($_SESSION['message'])) $_SESSION['message'] = '';

    $_SESSION['message'] .= $str . '<br>';
  }

  // メッセージを初期化
  public static function Clear()
  {
    unset($_SESSION['message']);
  }
}

// ==================
// 連想配列を初期化
// ==================
$players = array();
$cats = array();


// =========================
// プレイヤーもねこも共通部分
// =========================

abstract class Character
{
  protected $name;
  protected $hp; // プレイヤー：日暮れまでの時間、ねこ：警戒心

  public function getName()
  {
    return $this->name;
  }

  public function setHp($num)
  {
    $this->hp = $num;
  }

  public function getHp()
  {
    return $this->hp;
  }
}


// ==============
// プレイヤー側
// ==============

class Gender
{
  const boy = 0;
  const girl = 1;
  const cat = 2;
}

class Player extends Character
{

  protected $gender;
  protected $snackAttackMin;
  protected $danceAttackMin;
  protected $danceAttackMax;
  protected $healMin;
  Protected $healMax;

  public function __construct($name, $gender, $hp, $snackAttackMin, $snackAttackMax, $danceAttackMin, $danceAttackMax, $healMin, $healMax)
  {
    $this->name = $name;
    $this->gender = $gender;
    $this->hp = $hp;
    $this->snackAttackMin = $snackAttackMin;
    $this->snackAttackMax = $snackAttackMax;
    $this->danceAttackMin = $danceAttackMin;
    $this->danceAttackMax = $danceAttackMax;
    $this->healMin = $healMin;
    $this->healMax = $healMax;
  }


  // 行動選択

  // おやつをあげる
  public function snackAttack($target)
  {
    
    Message::set($this->getName() . 'はおやつをあげた!!');

    $attackPoint = mt_rand($this->snackAttackMin, $this->snackAttackMax);

    if (!mt_rand(0, 4)) {
      $attackPoint = $attackPoint * 2;
      Message::set($_SESSION['cat']->getName() . 'の心にクリティカルヒット!!');
    }

    $target->setHp($target->getHp() - $attackPoint);
    Message::set('おいしいおやつで警戒心が'. $attackPoint . 'ポイントさがった!');
  }

  // 一緒におどる
  public function danceAttack($target)
  {

    Message::set($this->getName() . 'はたのしく踊った!!');

    $attackPoint = mt_rand($this->danceAttackMin, $this->danceAttackMax);

    if (!mt_rand(0, 19)) {
      $attackPoint = $attackPoint * 2;
      Message::set($_SESSION['cat']->getName() . 'の心にとてもとてもクリティカルヒット!!');
    }

    $target->setHp($target->getHp() - $attackPoint);
    Message::set('すてきなダンスで' . $attackPoint . 'ポイント、警戒心がさがった!');
  }

  // 時間を巻き戻す
  public function healHp($target)
  {
    
    Message::set($this->getName() . 'は時間を巻き戻した!');

    $healPoint = mt_rand($this->healMin, $this->healMax);

    $target->setHp($target->getHp() + $healPoint);
    Message::set($healPoint . '分巻き戻った!');
  }
}



// ==============
// ねこ側
// ==============

// はりきりミケ
// 照れ屋のシャム
class attackCat extends Character
{
  protected $img;

  public function __construct($name, $hp, $runAttackMin, $runAttackMax, $sleepAttackMin, $sleepAttackMax, $img)
  {
    $this->name = $name;
    $this->hp = $hp;
    $this->runAttackMin = $runAttackMin;
    $this->runAttackMax = $runAttackMax;
    $this->sleepAttackMin = $sleepAttackMin;
    $this->sleepAttackMax = $sleepAttackMax;
    $this->img = $img;
  }

  // 対プレイヤーの行動
  public function attackPlayer($target)
  {

    if (!mt_rand(0, 5)) {

      // かけっこ
      Message::set($this->getName() . 'が、かけっこを始めた!!');
      $attackPoint = mt_rand($this->runAttackMin, $this->runAttackMax);

      // かけっこのクリティカル
      if (!mt_rand(0, 9)) {
        $attackPoint = $attackPoint * 0.5;
        $attackPoint = (int)$attackPoint;
        Message::set($_SESSION['player']->getName() . 'の心にクリティカルヒット');
      }

      $target->setHp($target->getHp() - $attackPoint);
      Message::set('一緒に走って' . $attackPoint . '分経過してしまった!!');

    } else {

      // うたたね
      Message::set($this->getName() . 'がうたた寝しだした!!');
      $attackPoint = mt_rand($this->sleepAttackMin, $this->sleepAttackMax);

      // うたた寝のクリティカル
      if (!mt_rand(0, 19)) {
        $attackPoint = $attackPoint * 2;
        Message::set($_SESSION['player']->getName() . 'の心にクリティカルヒット!!');
      }

      $target->setHp($target->getHp() - $attackPoint);
      Message::set('思わずうとうとして' . $attackPoint . '分経過してしまった!!');
    }
  }

  // 画像表示
  public function getImg()
  {
    return $this->img;
  }
}


// おすましユキ
class charmCat extends attackCat
{
  
  protected $charm;

  public function __construct($name, $hp, $runAttackMin, $runAttackMax, $sleepAttackMin, $sleepAttackMax, $img, $charm)
  {

    $this->charm = $charm;
    parent::__construct($name, $hp, $runAttackMin, $runAttackMax, $sleepAttackMin, $sleepAttackMax, $img);
  }

  public function attackPlayer($target)
  {
    
    if (!mt_rand(0, 5)) {

      // 魅了
      Message::set($this->getName() . 'のかわいいポーズ!');
      $charmPoint = $this->charm;
      
      $target->setHp($target->getHp() - $charmPoint);
      Message::set($target->getName() . 'はみとれて' . $charmPoint . '分経過してしまった!');

    } else {
      // その他の行動は親から継承
      parent::attackPlayer($target);
    }
  }
}


// のんびりプチ
// わんぱくサブ
class healCat extends charmCat
{
  
  protected $healMin;
  protected $healMax;

  public function __construct($name, $hp, $runAttackMin, $runAttackMax, $sleepAttackMin, $sleepAttackMax, $img, $charm, $healMin, $healMax)
  {
    $this->healMin = $healMin;
    $this->healMax = $healMax;
    parent::__construct($name, $hp, $runAttackMin, $runAttackMax, $sleepAttackMin, $sleepAttackMax, $img, $charm);

  }

  public function attackPlayer($target)
  {
    if (!mt_rand(0, 9)) {

      // 回復
      Message::set($this->name . 'がそっぽ向いた!!');
      $warningPoint = mt_rand($this->healMin, $this->healMax);
      debug('$warningPoint①'.print_r($warningPoint,true));

      $this->setHp($this->getHp() + $warningPoint);
      debug('$warningPoint②'.print_r($warningPoint,true));

      Message::set('警戒心が'.$warningPoint.'ポイント上がった!');
    } else {
      // その他の行動は親から継承
      parent::attackPlayer($target);
    }

  }
  
}



// ==============
// インスタンス
// ==============

// けんたくん（HPが他より高い）
// かすみちゃん（回復の値が他より高い）
// さすらいのねこ（攻撃力が他より高い）
$players[0] = new Player('けんたくん', Gender::boy, '600', '10', '20', '30', '60', '10','15');
$players[1] = new Player('かすみちゃん', Gender::girl, '480', '10', '20', '30', '60', '30','40');
$players[2] = new Player('さすらいのねこ', Gender::cat, '480', '20', '30', '50', '80', '10','15');

// はりきりミケ
// 照れ屋のシャム
$cats[] = new attackCat('はりきりミケ', '200', '10', '25', '30', '60', 'img/mike-walk.gif');
$cats[] = new attackCat('照れ屋のシャム', '250', '5', '10', '40', '60', 'img/gray-back.gif');

// おすましユキ（＋魅了攻撃）
$cats[] = new charmCat('おすましユキ', '150', '15', '20', '40', '70', 'img/white-cute.gif', '90');

// のんびりプチ
// わんぱくサブ（＋回復）
$cats[] = new healCat('のんびりプチ', '100', '5', '15', '10', '20', 'img/buchi-sleep.gif', '60', '20','50');
$cats[] = new healCat('わんぱくサブ', '150', '10', '15', '10', '30', 'img/black-run.gif', '70','30','60');


// ==============
//  関数
// ==============

// インスタンスからプレイヤーを作成
function createPlayer()
{
  global $players;
  global $boyFlg;
  global $girlFlg;
  global $catFlg;

  if ($boyFlg) {
    $_SESSION['player'] = $players[0];
  } else if ($girlFlg) {
    $_SESSION['player'] = $players[1];
  } else if ($catFlg) {
    $_SESSION['player'] = $players[2];
  }
}

// インスタンスからねこを作成
function createCat()
{
  debug('createCat関数スタート');
  global $cats;

  $cat = $cats[mt_rand(0, 4)];
  $_SESSION['cat'] = $cat;
  Message::set('あっ!!　' . $cat->getName() . 'がいるよ!!');
}

// ゲームスタート時に初期化させる
function init()
{
  debug('init関数スタート');
  Message::Clear();
  $_SESSION['friends'] = 0;
  createPlayer();
  createCat();
}

// ゲームオーバー
function gameOver()
{
  $_SESSION['gameover'] = true;
}

// リトライ
function retry()
{
  $_SESSION = array();
}


// ==============
//  実際の動き
// ==============

if (!empty($_POST)) {
  debug('POST送信あり');
  debug('$_SESSION' . print_r($_SESSION, true));
  Message::Clear();

  // キャラ選択
  $boyFlg = (!empty($_POST['boy'])) ? true : false;
  $girlFlg = (!empty($_POST['girl'])) ? true : false;
  $catFlg = (!empty($_POST['cat'])) ? true : false;
  // ゲーム内行動選択
  $snackAttackFlg = (!empty($_POST['snackAttack'])) ? true : false;
  $danceAttackFlg = (!empty($_POST['danceAttack'])) ? true : false;
  $healFlg = (!empty($_POST['heal'])) ? true : false;
  $escapeFlg = (!empty($_POST['escape'])) ? true : false;
  // リトライ 
  $retryFlg = (!empty($_POST['retry'])) ? true : false;


  // どのフラグがtrueになっているか
  if ($boyFlg || $girlFlg || $catFlg) {

    // キャラ選択後をしたときの処理
    debug('ゲーム開始');
    init();
  } else if ($snackAttackFlg || $danceAttackFlg || $healFlg || $escapeFlg) {

    // ゲーム内で行動選択をした時の処理
    switch (true) {

      case $snackAttackFlg:
        $_SESSION['player']->snackAttack($_SESSION['cat']);
        break;

      case $danceAttackFlg:
        $_SESSION['player']->danceAttack($_SESSION['cat']);
        break;

      case $healFlg:
        $_SESSION['player']->healHp($_SESSION['player']);
        break;

      case $escapeFlg:
        Message::set($_SESSION['cat']->getName() . 'とバイバイした、次のねこに会いに行こう!!　▼');
        createCat();
        break;
    }

    // ねこのHPが0になっているか判定
    if ($_SESSION['cat']->getHp() <= 0) {
      Message::set('警戒心がなくなって、ともだちになった!!　▼');
      $_SESSION['friends'] = $_SESSION['friends'] + 1;

      createCat();
    }

    // ねこ側の攻撃
    $_SESSION['cat']->attackPlayer($_SESSION['player']);

    // プレイヤーのHPが0になっているか判定
    if ($_SESSION['player']->getHp() <= 0) {
      gameOver();
    }
  } else if ($retryFlg) {
    retry();
  }

  // ブラウザの進む戻るで思わぬPOSTデータが送信されるのを防ぐ
  $_POST = array();
}

?>


<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>このこ、どのねこ？</title>
  <link rel="stylesheet" type="text/css" href="style.css">
  <link href="https://fonts.googleapis.com/css?family=M+PLUS+Rounded+1c" rel="stylesheet">
</head>

<body>

  <!-- セッションが空であればスタート画面 -->
  <?php if (empty($_SESSION)) : ?>

    <section class='back__ground back__ground__day'>

      <h1>このこ、どのねこ？</h1>

      <h2>キャラクター選択<br><span>夕暮れまでにたくさんのねこと仲良くなろう！</span></h2>

      <form method='post' action=''>

        <!-- type=image だとnameが＄_SESSIONに入らない -->
        <input type='submit' name='boy' class='character__input boys__input' value='けんたくん'>
        <img src="img/boy.gif" alt="" class='character__img'>

        <input type='submit' name='girl' class='character__input girls__input' alt="女の子の画像" value='かすみちゃん'>
        <img src="img/girl.gif" alt=""class='character__img'>


        <input type='submit' name='cat' class='character__input cats__input' alt="ねこの画像" value='さすらいのねこ'>
        <img src="img/cat.gif" alt="" class='character__img'>

      </form>
    </section>

  <?php else : ?>

    <!-- セッションが入っていれば -->

    <?php if (!empty($_SESSION['gameover'])) : ?>

      <!-- HPが０ならばゲームオーバー画面 -->
      <section class='back__ground back__ground__evening'>

        <h1>タイムアップ</h1>
        <h2><span>もう夕暮れだ！そろそろ帰らないと！<br>今日できた ともだち <?php echo $_SESSION['friends'] ?> 匹<span></h2>

        <form method='post'>
          <input type='submit' name='retry' value='リトライ？' class='retry__button'>

        </form>

      </section>

    <?php else : ?>
     


      <!-- そうでなければゲーム画面 -->
      <section class='back__ground back__ground__day'>

        <div class='hp__message'>
          <p>夕暮れまであと<?php echo $_SESSION['player']->getHp(); ?>分</p>
        </div>

        <div class='hp__message'>
          <p>ねこの警戒心: <?php echo $_SESSION['cat']->getHp(); ?>ポイント</p>
        </div>

        <form method='post' class='game__menu'>
          <input type="submit" name='snackAttack' value='おやつをあげる'>
          <input type="submit" name='danceAttack' value='一緒に踊る'>
          <input type="submit" name='heal' value='時間を巻き戻す'>
          <input type="submit" name='escape' value='バイバイする'>
        </form>

        <div class='game__cats__img__wrap'>
          <img src=<?php echo $_SESSION['cat']->getImg(); ?> class='game__cats__img'>
        </div>

        <div class='main__message'>
          <p><?php echo (!empty($_SESSION['message'])) ? $_SESSION['message'] : '' ?></p>
        </div>

      </section>
    <?php endif; ?>


  <?php endif; ?>

</body>

</html>
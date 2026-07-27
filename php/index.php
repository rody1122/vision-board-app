<?php

session_start();

if (!empty($_SESSION['id'])) {
    header(
        'Location: ../php/member.php'
        );
    exit;
}

$loginErrors    = $_SESSION['login_errors']    ?? [];
$registerErrors = $_SESSION['register_errors'] ?? [];

unset($_SESSION['login_errors'], 
      $_SESSION['register_errors']);
$openModal = '';

if (!empty($loginErrors))
    $openModal = 'login';
if (!empty($registerErrors)) 
    $openModal = 'register';
if (isset($_GET['modal']))   
    $openModal = $_GET['modal'];
?>
<!-- please dont change upper part! -->


<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,700;0,900;1,700;1,900&family=DM+Mono:wght@400;500&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="icon" href="../images/favicon.png">
    
    <!-- gsap resources -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.7/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.7/ScrollTrigger.min.js"></script>

    <title>M³, a place to visualize your ideas</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/chat.css">
</head>


<body>

<div id="loading-screen">
    <div class="loading-logo">
        M<sup>3</sup>
    </div>
    <div class="loading-bar-wrap">
        <div class="loading-bar"></div>
    </div>
    <div class="loading-words">
        <span>create</span><span>-</span><span>write</span><span>-</span><span>organize</span>
    </div>
</div>

<div id="cursor"></div>
<div id="cursor-ring"></div>

<!--probably needs to review here -->
<nav>
    <a href="#" class="nav-logo">
        M<sup>3</sup>
    </a>
    <ul class="nav-links">
        <li><a href="#features">
        <span class="shuffle-text" data-text="Features">Features</span></a></li>
        <li><a href="#how"><span class="shuffle-text" data-text="How it works">How it works</span></a></li>
        <li><a href="#" onclick="openModal('login'); return false;">
            <span class="shuffle-text" data-text="Sign in">Sign in</span></a></li>
    </ul>

    <button class="nav-btn" onclick="openModal('register')">
        Get started
    </button>
</nav>

<div class="chat-widget">
<button class="chat-fab" id="chatFab" aria-label="ヘルプ">?</button>

<div class="chat-box" id="chatBox" hidden>
<div class="chat-header">
<span>ヘルプ</span>
<button class="chat-close" id="chatClose" aria-label="閉じる">&times;</button>
</div>

<div class="chat-log" id="chatLog"></div>

<div class="chat-input-row">
<input type="text" id="chatInput" class="chat-input"
placeholder="質問を入力してください...">
<button class="chat-send" id="chatSend">送信</button>
</div>
</div>
</div>

<!-- fade away effect on the doooooooor -->
<div class="door-intro">
    <div class="door-intro-sticky">
        <img class="door-intro-img" src="../images/door3.png" alt="door">
         <div class="door-fade-overlay"></div>

    <div class="door-title">
        <h2>あなたのアイデアを</h2> <br>
        <p style="color: var(--yellow); font-style:italic; font-size: 6vh; text-shadow: 
    0 0 5px var(--text-muted),
    0 0 10px var(--terracotta),
    0 0 15px var(--card-yellow),
    0 0 18px var(--card-yellow),
    0 0 19px var(--card-yellow);">- 解き放つ扉 -</p>
    </div>

        <div class="door-scroll-hint">

            <div class="scroll-arrow"></div>
            スクロール ☟

        </div>
    </div>

<!-- gif section over here -->
    <!-- <section class="intro-gif">
        <img src="../images/intro.gif" alt="intro" class="fullscreen-gif">
    </section> -->


<!-- main page from here -->
<section class="hero">
    <div class="hero-inner">

        <h1 class="hero-title">
            イメージを形に<br>
            <p class="word-rose">
                作成する
            </p>
        </h1>

        <p class="hero-sub">
            アイデアを、いつでも残せるように。<br>
            ひらめきを、いつでも残せるように。
        </p>

        <div class="hero-actions">
            <button class="btn-primary" onclick="openModal('register')">
                ビジョンボードを作る →
            </button>
            <button class="btn-secondary" onclick="openModal('login')">
                アカウント持ってる方へ
            </button>
        </div>
    </div>
</section>

<div class="marquee-section">
    <div class="marquee-track">
        <div class="marquee-item">
            <span class="marquee-word c-rose">
                   Create .  
            </span>
            <span class="marquee-word">
                   Organize .  
            </span>
            <span class="marquee-word c-yellow">
                   Remember .  
            </span>
            <span class="marquee-word">
                   Vision board .  
            </span>
            <span class="marquee-word c-green">
                   Write .  
            </span>
            <span class="marquee-word">
               Plan .  
            </span>
            <span class="marquee-word c-rose">
                   Inspire .  
            </span>
            <span class="marquee-word">
                   Design .  
            </span>
            <span class="marquee-word c-yellow">
                   Edit .  
            </span>
            <span class="marquee-word">
                   Share .  
            </span>
        </div>

        <div class="marquee-item" aria-hidden="true">
            <span class="marquee-word c-rose">
                   Create .  
            </span>
            <span class="marquee-word">
                   Organize .  
            </span>
            <span class="marquee-word c-yellow">
                   Remember .  
            </span>
            <span class="marquee-word">
                   Vision board .  
            
            </span>
            <span class="marquee-word c-green">
                   Write .  
            </span>
            <span class="marquee-word">
                   Vision .  
            </span>
            <span class="marquee-word c-rose">
                   Inspire .  
            </span>
            <span class="marquee-word">
                   Design .   
            </span>
            <span class="marquee-word c-yellow">
                   Edit .   
            </span>
            <span class="marquee-word">
                   Share .  
            </span>
        </div>
    </div>
</div>

<!-- features and explanations, grid style for easy understanding -->
<section class="features" id="features">
    <span class="section-eyebrow">- はじめに -</span>

    <h2 class="section-heading" style="font-size: 14vh;">
        All you need<br>
        in one place
    </h2>

    <!-- from here, the grids -->
    <div class="features-grid">

        <div class="feature-card">
            <div class="feature-title">Visual boards</div>
                <p class="feature-desc">
                キャンバス上に写真、メモ、アイデアを集めましょう。ドラッグ＆ドロップで、自分好みに自由に配置できます。
                </p>
        </div>

        <div class="feature-card">
            <div class="feature-title">Polaroid cards</div>
                <p class="feature-desc">
                それぞれの画像は、そのスタイルに応じたキャプションとカテゴリーが付いた、ポラロイド風のカードになります。
                </p>
        </div>

        <div class="feature-card">
            <div class="feature-title">Inline edition</div>
                <p class="feature-desc">
                ワンクリックで編集可能なカード。お好みに合わせてカスタマイズできます。
                </p>
        </div>

        <div class="feature-card">
            <div class="feature-title">
                Sticker library</div>
                <p class="feature-desc">
                カードにステッカーや絵文字を追加できます。より視覚的に魅力的なカードに仕上げることができます。
                </p>
        </div>

        <div class="feature-card">
            <div class="feature-title">Categories</div>
                <p class="feature-desc">
                テーマ、主題、または写真でグループ化できます。選択した項目のみを表示するようにボードをフィルタリングすることもできます。 
                </p>
        </div>

        <div class="feature-card">
            <div class="feature-title">Dark & Light</div>
                <p class="feature-desc">
                    明るさと暗さが交互に変化し、時間帯に関係なく視覚的に快適に感じられるように調整されています。
                </p>
        </div>
    </div>
</section>


<div class="text-reveal-section">
    <span class="reveal-line">
        せっかく思いついたアイデアが、</span>
    <span class="reveal-line muted">
        実行する前に</span>
    <span class="reveal-line accent">
        消えてしまったことはありませんか？</span>
    <br>

    <span class="reveal-line" style="margin-top:32px">
        M³はそんなあなたのための場所です。</span>

    <span class="reveal-line muted">
        アイデアや写真を一つにまとめ、</span>
    <span class="reveal-line">
        自由に配置して、</span>
    <span class="reveal-line muted">
        見える形で</span>
    <span class="reveal-line">
        整理できます。</span>
</div>

<!-- circle transaction, just to show up  -->
<div class="circle-transition-section">
    <div class="circle-logo">
        M<sup>3</sup>
    </div>
    <div class="circle-fill"></div>
</div>

<!-- gsap explanation sliding cards t the left -->
<div class="cards-horizontal-section" id="how">
    <div class="cards-strip">

        <div class="step-card-full" data-num="01">
            <div class="step-card-inner">
                <span class="step-label">Step 01: The beginning</span>
                <div class="step-title">アカウントを作成する</div>
                <p class="step-desc">
                    メールアドレスとパスワードだけ。カードも不要、面倒な手続きも一切なし。<br>
                    数秒でM³にアクセスできます。
                </p>
            </div>
        </div>

        <div class="step-card-full" data-num="02">
            <div class="step-card-inner">
                <span class="step-label">Step 02: Upload</span>
                <div class="step-title">写真をアップする</div>
                <p class="step-desc">
                    デバイスから任意の画像を選択してください。<br>
                    M³が自動的にポラロイドカードに変換します。
                </p>
            </div>
        </div>

        <div class="step-card-full" data-num="03">
            <div class="step-card-inner">
                <span class="step-label">Step 03: Customize</span>
                <div class="step-title">ポラロイドをあなたらしくカスタマイズする。</div>
                <p class="step-desc">
                    キャプションを書き、カテゴリを選択し、写真をカスタマイズして、自分だけのスタイルに仕上げましょう。
                </p>
            </div>
        </div>

        <div class="step-card-full" data-num="04">
            <div class="step-card-inner">
                <span class="step-label">Step 04: Board</span>
                <div class="step-title">ボードを作成する</div>
                <p class="step-desc">
                    まるで時間とともに成長していく日記のように、編集したり、整理したり、再編成したりできます。
                </p>
            </div>
        </div>

    </div>
</div>

<!-- soft circles, background effect. max 3 colors to add! remember to edit it here later -->
<section class="cta-section">
    <div class="cta-blob-1"></div>
    <div class="cta-blob-2"></div>
        <h2 class="cta-title reveal" style="border-bottom: 3px;">
            ボードを作成する<br>
            準備は
            <em>出来ましたか？</em>
        </h2>

    <p class="cta-sub reveal">
        永久無料です。今すぐ登録しましょう！
    </p>

    <button class="btn-primary reveal" onclick="openModal('register')">
        ボードを作成する →
    </button>
</section>

<!-- footer part, might add some other companies as support just for fun? -->
<footer>
    <div class="footer-top">
        <div>
            <a href="#" class="footer-logo">
                M<sup>3</sup></a>
            <p class="footer-tagline">
                An open door for your ideas.
            </p>
        </div>

        <ul class="footer-links">
            <li><a href="#features">
                Features
            </a></li>
            <li><a href="#how">
                How it works
            </a></li>
            <li><a href="#" onclick="openModal('login'); return false;">
                Login
            </a></li>
        </ul>
    </div>

    <!-- create a real email or just leave as it is? -->
    <div class="footer-email-wrap">
        <a href="mailto:m3creative@gmail.com" class="footer-email">
            m3creative@gmail.com
        </a>

        <span class="copy-badge">
            Copy</span>
    </div>
    <div class="footer-bottom">

        <span class="footer-copy">
            © 2026 M³ Creative
        </span>

        <span class="policy" style="color:white; font-size:2vh; text-decoration:none;"> 
            <a href="../html/policy.html" style="color: gray; text-decoration: none; ">プライベートポリシー</a>
        </span>
    </div>
</footer>

<!-- modal part, mostly login section only for now -->
<div class="modal-overlay" id="authModal" onclick="handleOverlayClick(event)">
    <div class="modal">
        <button class="modal-close" onclick="closeModal()">✕</button>
        <div class="modal-tabs">

            <button class="modal-tab active" id="tab-login" onclick="switchTab('login')">Log in</button>

            <button class="modal-tab" id="tab-register" onclick="switchTab('register')">Sign up</button>
        </div>

        <div class="modal-panel active" id="panel-login">
            <?php if (!empty($loginErrors)): ?>
                <div class="form-errors">
                    <?php foreach ($loginErrors as $e): ?><div class="form-error">↳ 
                        <?= htmlspecialchars($e) ?></div>
                        <?php endforeach; ?></div>
            <?php endif; ?>

<!-- login section -->
            <form method="POST" action="../php/login.php">
                <div class="form-group">
                    <label class="form-label">メール</label>
                        <input type="email" name="email" class="form-input" placeholder="you@example.com" required>
                </div>

                <div class="form-group">
                    <label class="form-label">パスワード</label>
                        <input type="password" name="password" class="form-input" placeholder="••••••" required>
                </div>

                <button type="submit" class="form-submit">Enter →</button>
            </form>


            <div class="modal-switch">アカウント持ってない方はこちらへ 
                <button onclick="switchTab('register')">Register</button>
            </div>
        </div>


        <div class="modal-panel" id="panel-register">
            <?php if (!empty($registerErrors)): ?>
                <div class="form-errors">
                    <?php foreach ($registerErrors as $e): ?>
                        <div class="form-error">↳ <?= htmlspecialchars($e) ?></div>
                        <?php endforeach; ?>
                </div>
            <?php endif; ?>


<!-- sign up section -->
            <form method="POST" action="../php/register.php">
                <div class="form-group">
                    <label class="form-label">名前</label>
                    <input type="text" name="user_name" class="form-input" placeholder="名前" required>
                </div>

                <div class="form-group">
                    <label class="form-label">メール</label>
                    <input type="email" name="email" class="form-input" placeholder="your_best_email@example.com" required>
                </div>

                <div class="form-group">
                    <label class="form-label">メール確認</label>
                    <input type="email" name="email_confirm" class="form-input" placeholder="メール確認" required>
                </div>

                <div class="form-group">
                    <label class="form-label">パスワード</label>
                    <input type="password" name="password" class="form-input" placeholder="min. 6 characters" minlength="6" required>
                </div>

                <div class="form-group">
                    <label class="form-label">パスワード確認</label>
                    <input type="password" name="password_confirm" class="form-input" placeholder="パスワード確認" minlength="6" required>
                </div>

                <button type="submit" class="form-submit">アカウントを作成する →</button>
            </form>

            <div class="modal-switch">アカウントを持っている方は 
                <button onclick="switchTab('login')">ログイン</button>
            </div>
        </div>
    </div>
</div>

<script src="../js/script.js"></script>
<script src="../js/chat.js"></script>


<?php if ($openModal): ?>
<script>
document.addEventListener('DOMContentLoaded', () => 
    openModal('<?= $openModal ?>'));
</script>
<?php endif; ?>

</body>
</html>

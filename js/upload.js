'use strict';

// grabbing everything we need from the page
// ページ内で必要な要素をすべて取得する
const dropzone  = document.getElementById('dropzone');
const fileInput = document.getElementById('fileInput');
const fileArea  = document.getElementById('fileArea');
const addFile   = document.getElementById('addFile');

// this keeps track of every photo added so far
// we need it because input.files alone cant be edited directly
// ページ内で必要な要素をすべて取得する
// input.files は直接編集できないため、DataTransfer を使う
let dt = new DataTransfer();

// small rotations so the previews look like scattered polaroids
// プレビューを散らばったポラロイド写真のように見せるための小さな回転角度
const rotations = [-2, 1.5, -1, 2, -1.5, 0.5];


// clicking the button or the zone itself opens the file picker
// 追加ボタンまたはドロップエリアをクリックするとファイル選択画面を開く
addFile.addEventListener('click', function (e) {
    e.stopPropagation();
    fileInput.click();
});

dropzone.addEventListener('click', function () {
    fileInput.click();
});


// picking files the normal way
// 通常のファイル選択で画像を追加する
fileInput.addEventListener('change', function () {
    addFiles(fileInput.files);
});


// drag and drop part starts here
// ここからドラッグ＆ドロップの処理

// the dragover needs preventDefault or the browser just opens the image
// dragover では preventDefault() が必要
// これがないとドロップを受け付けられず、ブラウザの標準動作が実行される
dropzone.addEventListener('dragover', function (e) {
    e.preventDefault();
    dropzone.classList.add('dragover');
});

dropzone.addEventListener('dragleave', function () {
    dropzone.classList.remove('dragover');
});

dropzone.addEventListener('drop', function (e) {
    e.preventDefault();
    dropzone.classList.remove('dragover');
    addFiles(e.dataTransfer.files);
});

// ブラウザの標準動作の実行を禁止
document.addEventListener('dragover', function (e) {
    e.preventDefault();
});

document.addEventListener('drop', function (e) {
    e.preventDefault();
});


// puts new files into our list, skipping anything that isnt an image
// 新しいファイルを一覧へ追加する
// 画像以外のファイルは追加せずに飛ばす
function addFiles(files) {
    const errorMessage = document.getElementById('error-message');
    for (const file of files) {
        if (!file.type.startsWith('image/')) {
        // エラーメッセージを表示
        errorMessage.textContent = '画像以外はアップロードできません';
        errorMessage.style.display = 'block';
        continue;
        }

        errorMessage.style.display = 'none';
        errorMessage.textContent = '';

        dt.items.add(file);
    }

    // this is the trick, we hand the whole list back to the real input
    // so the form sends everything as img_path[] when submitted
    // ここが重要な処理
    // DataTransfer が持っているファイル一覧を実際の input に戻す
    // これにより、フォーム送信時にすべてのファイルが img_path[] として送信される
    fileInput.files = dt.files;

    console.log(dt.files);
        console.log(fileInput.files);

    renderPreviews();
}


// rebuilds the little polaroid previews from scratch
// 小さなポラロイド風プレビューを最初から作り直す
function renderPreviews() {
    fileArea.innerHTML = '';

    Array.from(dt.files).forEach(function (file, i) {
        const item = document.createElement('div');
        item.classList.add('preview-item');
        item.style.setProperty('--rot', rotations[i % rotations.length] + 'deg');

        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);

        // freeing memory after the thumbnail loads
        // サムネイル画像の読み込み後に一時URLを解放し、メモリを節約する
        img.onload = function () { URL.revokeObjectURL(img.src); };

        const name = document.createElement('div');
        name.classList.add('preview-name');
        name.textContent = file.name;

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.classList.add('preview-remove');
        removeBtn.textContent = '✕';
        removeBtn.addEventListener('click', function () {
            removeFile(i);
        });

        item.appendChild(removeBtn);
        item.appendChild(img);
        item.appendChild(name);
        fileArea.appendChild(item);
    });
}


// removing one photo means rebuilding the list without it
// 画像を1枚削除するときは、その画像を除外してファイル一覧を作り直す
function removeFile(index) {
    const newDt = new DataTransfer();

    Array.from(dt.files).forEach(function (file, i) {
        if (i !== index) newDt.items.add(file);
    });

    dt = newDt;
    fileInput.files = dt.files;
    renderPreviews();
}

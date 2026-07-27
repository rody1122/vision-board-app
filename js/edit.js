'use strict';

const imageIdInput = document.getElementById('image_id');

const addImageForm = document.getElementById('add-image-form');
const addImageBtn = document.getElementById('add-image-btn');

const sendTitle = document.getElementById('sendTitle');
const sendContents = document.getElementById('sendContents');

const noteTitle = document.getElementById('noteTitle');
const noteContents = document.getElementById('noteContents');

let selectedImage = null;

// ボード上の画像（画像・スタンプ）がクリックされたか確認
document.addEventListener('click', function(e) {
    
    if(e.target.classList.contains('board-img')) {
        selectedImage = e.target;
        imageIdInput.value = e.target.dataset.imgId;
        console.log(imageIdInput.value);
    }
});

// 画像追加画面へ遷移してもタイトル・メモを保持する
addImageBtn.addEventListener('click', function() {
    // 入力中のタイトル・メモをhiddenへコピー
    sendTitle.value = noteTitle.value;
    sendContents.value = noteContents.value;
// console.log(addImageForm.action);
// console.log(addImageForm.method);
    // list.phpへ送信
    addImageForm.submit();
})


// side bar hidding section
// needs some adjustment later
//サイドバーを非表示にするセクション
//調整レイヤーが必要です
const focusToggle = document.getElementById('focusToggle');

if (focusToggle) {

focusToggle.addEventListener('click', function () {
const on = document.body.classList.toggle('focus-mode');
focusToggle.value = on ? '\u26F6 元に戻す' : '\u26F6 全画面表示モード';

});
}

// from here just sticker parts and editable area
// ここから先はステッカーパーツと編集可能な領域のみ

const stickerToggle = document.getElementById('stickerToggle');
const stickerPanel = document.getElementById('stickerPanel');
const boardCanvasEl = document.getElementById('boardCanvas');

if (stickerToggle && stickerPanel) {

stickerToggle.addEventListener('click', function () {
stickerPanel.hidden = !stickerPanel.hidden;
});

document.addEventListener('click', function (e) {
    if (!stickerPanel.hidden
    && !stickerPanel.contains(e.target)
    && e.target !== stickerToggle) {
    stickerPanel.hidden = true;
}
});

document.querySelectorAll('.sticker-choice').forEach(function (choice) {
choice.addEventListener('click', function () {
const sticker = choice.dataset.sticker;
const noteId = document.getElementById('noteId');
const libraryNoteId = document.getElementById('libraryNoteId');

console.log('送信note_id =', noteId.value);
$.ajax({
    url: 'add_sticker.php',
    type: 'post',
    data: {
        sticker: sticker,
        note_id: noteId.value
    },
    dataType: 'json'
})

.done(function (res) {
    if (res.status !== 'success') {
        alert('スタンプを追加できませんでした');
return;
}
noteId.value = res.note_id;
libraryNoteId.value = res.note_id;

const img = document.createElement('img');
img.src = res.img_path;
img.className = 'board-img';
img.draggable = false;
img.dataset.imgId = res.img_id;
img.dataset.angle = 0;
img.style.cssText =

'width:150px;height:150px;position:absolute;' +
'left:50px;top:50px;z-index:1;transform:rotate(0deg);';

boardCanvasEl.appendChild(img);
stickerPanel.hidden = true;
})

// boardCanvasEl.appendChild(img);
// stickerPanel.hidden = false;
// })
// here might need to delete it later

.fail(function () {
    alert('スタンプを追加できませんでした');
         });
    });
});
}

// ボードから画像を外す
const removeImageForm = document.getElementById('remove-image-form');

removeImageForm.addEventListener('submit', function(e) {
    e.preventDefault();

    if (!selectedImage || !imageIdInput.value) {
    alert('外す画像を選択してください');
    return;
    }

    $.ajax({
        url: 'remove_image.php',
        type: 'post',
        data: {
            image_id: imageIdInput.value
        },
        dataType: 'json'
    })
    .done(function(data) {
        if(data.status === 'success' && selectedImage) {
            selectedImage.remove();

            selectedImage = null;
            imageIdInput.value = '';
        }
    })
    .fail(function(error) {
        alert('削除できませんでした');
    })
});
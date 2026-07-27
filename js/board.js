'use strict';

// ドラッグ用
let isDragging = false;
let dragImg = null;

let maxZ = 1;


let selectImg = null;

let offsetX = 0;
let offsetY = 0;


function highlightSelected(img) {
    document.querySelectorAll('.board-img.is-selected').forEach(function (el) {
        el.classList.remove('is-selected');
    });
    if (img) img.classList.add('is-selected');
}


// ボタン取得
const saveBtn = document.getElementById('save-btn');
const plusSize = document.getElementById('plusSize');
const minusSize = document.getElementById('minusSize');
const rotateRight = document.getElementById('rotateRight');
const rotateLeft = document.getElementById('rotateLeft');

// board canvas, needed for the background picker below
const boardCanvas = document.getElementById('boardCanvas');


document.addEventListener('mousedown', function (event) {

    if (!event.target.classList.contains('board-img')) return;

    isDragging = true;
    dragImg = event.target;
    offsetX = event.clientX - dragImg.offsetLeft;
    offsetY = event.clientY - dragImg.offsetTop;


    selectImg = dragImg;
    highlightSelected(dragImg);

});


document.addEventListener('mousemove', function(event) {
    if(isDragging && dragImg) {

        let newX = event.clientX - offsetX;
        let newY = event.clientY - offsetY;

        let maxX = boardCanvas.offsetWidth - dragImg.offsetWidth;
        let maxY = boardCanvas.offsetHeight - dragImg.offsetHeight;

        if (newX < 0) {
            newX = 0;
        } else if (newX > maxX) {
            newX = maxX;
        } 

        if (newY < 0) {
            newY = 0;
        } else if (newY > maxY) {
            newY = maxY;
        }

        dragImg.style.left = newX + 'px';
        dragImg.style.top = newY + 'px';

    }
});


document.addEventListener('mouseup', function(event) {

    isDragging = false;
    dragImg = null;
});


document.addEventListener('click', function(event) {
    if (!event.target.classList.contains('board-img')) return;

    selectImg = event.target;
    highlightSelected(selectImg);

    maxZ++;
    event.target.style.zIndex = maxZ;
});


// 選択画像を大きくする
plusSize.addEventListener('click', function() {

    if (selectImg === null) return;

    let width = selectImg.offsetWidth;
    let height = selectImg.offsetHeight;

    width += 20;
    height += 20;

    selectImg.style.width = width + 'px';
    selectImg.style.height = height + 'px';
});


minusSize.addEventListener('click', function() {

    if (selectImg === null) return;

    let width = selectImg.offsetWidth;
    let height = selectImg.offsetHeight;

    width -= 20;
    height -= 20;

    selectImg.style.width = width + 'px';
    selectImg.style.height = height + 'px';
});


// 時計回りに回転
rotateRight.addEventListener('click', function() {

    if(selectImg === null) return;

    let angle = parseInt(selectImg.dataset.angle);

    angle += 10;

    selectImg.dataset.angle = angle;
    selectImg.style.transform = `rotate(${angle}deg)`;

});

// 反時計回りに回転
rotateLeft.addEventListener('click', function() {

    if(selectImg === null) return;

    let angle = parseInt(selectImg.dataset.angle);

    angle -= 10;

    selectImg.dataset.angle = angle;
    selectImg.style.transform = `rotate(${angle}deg)`;

});


// each swatch button carries its css value in data-bg.
// clicking paints the canvas right away, and the value rides
// along with the save request so it survives a reload
document.querySelectorAll('.bg-swatch').forEach(function(swatch) {
    swatch.addEventListener('click', function() {
        const bg = swatch.dataset.bg;

        boardCanvas.style.background = bg;
        boardCanvas.dataset.bg = bg;

        // little visual feedback on which swatch is active
        document.querySelectorAll('.bg-swatch').forEach(function(s) {
            s.classList.remove('active');
        });
        swatch.classList.add('active');
    });
});

saveBtn.addEventListener('click', function() {


    let noteId = document.getElementById('noteId').value ;
    let noteTitle = document.getElementById('noteTitle').value;
    let noteContents = document.getElementById('noteContents').value;

    const positions = [];
    const boardImgs = document.querySelectorAll('.board-img');


    boardImgs.forEach(function(img) {
        positions.push({
        imgId : img.dataset.imgId,
        x : parseInt(img.style.left),
        y : parseInt(img.style.top),
        width : img.offsetWidth,
        height : img.offsetHeight,
        zIndex : parseInt(img.style.zIndex),
        angle : parseInt(img.dataset.angle)
        });
    });

    const positionsJson = JSON.stringify(positions);

    // AjaxでPHPへ送信
    $.ajax({
            url: 'save_position.php',
            type: 'post',
            data: {
                saveBoard: 1,
                noteId: noteId,
                noteTitle: noteTitle,
                noteContents: noteContents,
                positions: positionsJson,
                bgStyle: boardCanvas.dataset.bg
            },
            dataType: 'json'
    })
    .done(function(data) {
        if (data.status === 'success') {
            $('#save-message').text('保存しました');
             document.getElementById('noteId').value = data.note_id;
            const addImageId = document.querySelector('#add-image-form input[name="id"]');
            addImageId.value = data.note_id;
    }
    })
    .fail(function(error) {
        alert('データを取得できませんでした');
        console.log(error);
    });
});

'use strict';

// the canvas is always 1000x700 on the inside, same size the board
// was edited on. here we just shrink it to fit the frame, so the
const viewFrame = document.getElementById('viewFrame');
const viewCanvas = document.getElementById('viewCanvas');

const BASE_W = 1000;
const BASE_H = 700;

function fitCanvas() {
    if (!viewFrame || !viewCanvas) return;

    const scale = viewFrame.clientWidth / BASE_W;

    viewCanvas.style.transform = 'scale(' + scale + ')';

    // the frame needs a real height, otherwise the scaled canvas leaves a big empty gap under it
    viewFrame.style.height = (BASE_H * scale) + 'px';
}

fitCanvas();
window.addEventListener('resize', fitCanvas);


// save the board as a png 
// view.js shrinks the canvas with a scale to fit the frame, so before
// html2canvas reads it we bump it back to full 1000x700, then put the scale
// right back so the page doesnt jump
// ボードをPNG形式で保存 
// view.jsはフレームに合わせてキャンバスを縮小するので、
// html2canvasが読み込む前に、キャンバスを元の1000x700に戻し、
// ページがジャンプしないようにスケールを元に戻します。
const downloadBtn = document.getElementById('downloadBtn');

if (downloadBtn) {
    downloadBtn.addEventListener('click', function () {

        // if the library didnt load (blocked, offline...) just tell the user
        // instead of throwing and leaving the button silently dead
        if (typeof html2canvas === 'undefined') {
            alert('画像の保存機能を読み込めませんでした。ページを再読み込みしてください。');
            return;
        }

        const savedTransform = viewCanvas.style.transform;
        viewCanvas.style.transform = 'none';

        // filename from the board title, falling back to a plain name.

        
        const titleEl = document.querySelector('.view-title');
        let name = titleEl ? titleEl.textContent.trim() : 'board';
        name = name.replace(/[\\/:*?"<>|]/g, '_');
        if (!name) name = 'board';

        html2canvas(viewCanvas, {
            width: BASE_W,
            height: BASE_H,
            scale: 2,
            useCORS: true,
            backgroundColor: null
        }).then(function (canvas) {
            const link = document.createElement('a');
            link.download = name + '.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
        }).catch(function (err) {
            alert('画像の保存に失敗しました。');
            console.log(err);
        }).then(function () {
            // runs whether it worked or not, so the board never stays stretched
            viewCanvas.style.transform = savedTransform;
        });
    });
}

'use strict';

const BOARD_W = 1000;

function fitMiniBoards() {
    document.querySelectorAll('.board-thumb-wrapper').forEach(wrapper => {
        const canvas = wrapper.querySelector('.mini-board-canvas');
        if (!canvas) return;

        const scale = wrapper.clientWidth / BOARD_W;
        canvas.style.transform = 'scale(' + scale + ')';
    });
}

document.addEventListener('DOMContentLoaded', fitMiniBoards);
window.addEventListener('resize', fitMiniBoards);

// check later for compatibilities
function deleteSelectedBoards() {
    const checkboxes = document.querySelectorAll('.board-delete-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('削除したいボードにチェックを入れてください。');
        return;
    }
    if (confirm('選択された ' + checkboxes.length + ' 件のボードを完全に削除しますか？\n※配置されていたパーツ画像は自動的に画像倉庫へ戻ります。')) {
        const form = document.getElementById('delete-bulk-form');
        checkboxes.forEach(cb => {

            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'note_ids[]';
            hiddenInput.value = cb.value;
            form.appendChild(hiddenInput);

        });
        form.submit();
    }
}

// すべてのボードを選択
const selectAllBoards = document.getElementById('select-all-boards');
const boardChecks = document.querySelectorAll('.board-delete-checkbox');


if(selectAllBoards) {
    // 「すべて選択」の状態に合わせて、全ボードの選択状態を切り替える
    selectAllBoards.addEventListener('change', function() {
        boardChecks.forEach(function(boardCheck) {
            boardCheck.checked = selectAllBoards.checked;
        });
    });

    // 各ボードのチェックが変更されたときの処理
    boardChecks.forEach(function(boardCheck) {
        boardCheck.addEventListener('change', function() {
            const isAllChecked = Array.from(boardChecks).every(function(check) {
                return check.checked;
            });
            selectAllBoards.checked = isAllChecked;
        });
    });
}
'use strict';

// these two functions are the girls' original inline script from list.php,
// just living in their own file now. the logic is the same

function goToEditPage() {
    const form = document.getElementById('parts-form');
    const checkboxes = form.querySelectorAll('input[name="image_ids[]"]:checked');

    if (checkboxes.length === 0) {
        alert('パーツが選択されていません。編集するパーツにチェックを入れてください。');
        return;
    }

    form.action = 'edit.php';
    form.method = 'POST';
    form.submit();
}

function deleteSelectedParts() {
    const form = document.getElementById('parts-form');
    const checkboxes = form.querySelectorAll('input[name="image_ids[]"]:checked');

    if (checkboxes.length === 0) {
        alert('削除したいパーツにチェックを入れてください。');
        return;
    }

    if (confirm('選択された ' + checkboxes.length + ' 件のパーツを倉庫から完全に削除しますか？')) {
        form.action = 'delete_image.php';
        form.method = 'POST';
        form.submit();
    }
}

// すべてのパーツを選択
const selectAllParts = document.getElementById('select-all-parts');
const partChecks = document.querySelectorAll('.part-check');

// 「すべて選択」の状態に合わせて、全パーツの選択状態を切り替える
selectAllParts.addEventListener('change', function() {
    partChecks.forEach(function(partCheck) {
        partCheck.checked = selectAllParts.checked;
    });
});

// 追加：各パーツのチェックが変更されたときの処理
partChecks.forEach(function(partCheck) {
    partCheck.addEventListener('change', function() {
        const isAllChecked = Array.from(partChecks).every(function(check) {
            return check.checked;
        });
        selectAllParts.checked = isAllChecked;
    });
});
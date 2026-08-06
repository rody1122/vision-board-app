const toggle = document.getElementById('themeToggle');
const label = document.getElementById('theme-label');
const html = document.documentElement;

// Remember preference
const savedTheme = localStorage.getItem('m3-theme') || 'light';
html.setAttribute('data-theme', savedTheme);

label.textContent = savedTheme === 'dark' ? 'dark' : 'light';

toggle.addEventListener('click', () => {
    const current = html.getAttribute('data-theme');
    const next = current === 'light' ? 'dark' : 'light';
    html.setAttribute('data-theme', next);

    localStorage.setItem('m3-theme', next);
    label.textContent = next === 'dark' ? 'dark' : 'light';
});


const resumeBoard = document.getElementById('resume-board');
const discardBoard = document.getElementById('discard-board');
const draftModal = document.getElementById('draft-modal'); 

if(draftModal) {
    const noteId = draftModal.dataset.noteId;

    // 編集を続ける場合は編集中のボードに移動
    resumeBoard.addEventListener('click', function() {
        console.log('編集ページに移動');

        location.href = `edit.php?id=${noteId}`;
    });

    // 編集中ボードを破棄
    discardBoard.addEventListener('click', function() {
        console.log(noteId);

        $.ajax({
            url: 'delete_draft.php',
            type: 'post',
            data: {
                noteId: noteId
            },
            dataType: 'json'
        })
        .done(function(res) {
            if(res.status === 'success') {
                location.reload();
            }
        })
        .fail(function(error) {
            alert('破棄できませんでした');
        });
    });
}
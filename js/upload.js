'use strict';

// grabbing everything we need from the page
const dropzone  = document.getElementById('dropzone');
const fileInput = document.getElementById('fileInput');
const fileArea  = document.getElementById('fileArea');
const addFile   = document.getElementById('addFile');

// this keeps track of every photo added so far
// we need it because input.files alone cant be edited directly
let dt = new DataTransfer();

// small rotations so the previews look like scattered polaroids
const rotations = [-2, 1.5, -1, 2, -1.5, 0.5];


// clicking the button or the zone itself opens the file picker
addFile.addEventListener('click', function (e) {
    e.stopPropagation();
    fileInput.click();
});

dropzone.addEventListener('click', function () {
    fileInput.click();
});


// picking files the normal way
fileInput.addEventListener('change', function () {
    addFiles(fileInput.files);
});


// drag and drop part starts here
// the dragover needs preventDefault or the browser just opens the image
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


// puts new files into our list, skipping anything that isnt an image
function addFiles(files) {
    for (const file of files) {
        if (!file.type.startsWith('image/')) continue;
        dt.items.add(file);
    }

    // this is the trick, we hand the whole list back to the real input
    // so the form sends everything as img_path[] when submitted
    fileInput.files = dt.files;

    renderPreviews();
}


// rebuilds the little polaroid previews from scratch
function renderPreviews() {
    fileArea.innerHTML = '';

    Array.from(dt.files).forEach(function (file, i) {
        const item = document.createElement('div');
        item.classList.add('preview-item');
        item.style.setProperty('--rot', rotations[i % rotations.length] + 'deg');

        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        // freeing memory after the thumbnail loads
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
function removeFile(index) {
    const newDt = new DataTransfer();

    Array.from(dt.files).forEach(function (file, i) {
        if (i !== index) newDt.items.add(file);
    });

    dt = newDt;
    fileInput.files = dt.files;
    renderPreviews();
}

// stops an empty submit before it even reaches php,
// the backend would answer with its own message anyway,
// this just saves the round trip
const uploadForm = document.getElementById('uploadForm');

if (uploadForm) {
    uploadForm.addEventListener('submit', function (e) {
        if (dt.files.length === 0) {
            e.preventDefault();
            alert('写真を選んでから保存してください。');
        }
    });
}

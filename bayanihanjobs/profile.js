// JavaScript for handling profile modals

// Create blur background
const blurBackground = document.createElement('div');
blurBackground.id = 'blur-background';
document.body.appendChild(blurBackground);

// Edit Profile Modal
document.getElementById('edit-profile-btn').addEventListener('click', () => {
    document.body.classList.add('modal-active');
    document.getElementById('edit-profile-modal').style.display = 'block';
});

document.getElementById('cancel-edit').addEventListener('click', () => {
    document.body.classList.remove('modal-active');
    document.getElementById('edit-profile-modal').style.display = 'none';
});

// Update Skills Modal
document.getElementById('update-skills-btn').addEventListener('click', () => {
    document.body.classList.add('modal-active');
    document.getElementById('update-skills-modal').style.display = 'block';
});

document.getElementById('cancel-skills-edit').addEventListener('click', () => {
    document.body.classList.remove('modal-active');
    document.getElementById('update-skills-modal').style.display = 'none';
});



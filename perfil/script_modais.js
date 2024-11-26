function openModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function openAddPermissionModal(permissionType) {
    document.getElementById('permissionType').value = permissionType;
    document.getElementById('addPermissionType').innerText = permissionType;
    openModal('addPermissionModal');
}

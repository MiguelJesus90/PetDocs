/**
 * PetDocs Application
 * Developed by Miguel Jesús Arias Cañete
 */

// API Configuration
const API_BASE = 'https://petdocs-miguel.lovestoblog.com/backend/'; // InfinityFree URL (Renamed from api to backend)

// State
let currentPetId = null;

// Initialize app
document.addEventListener('DOMContentLoaded', () => {
    loadPets();
});

// ========== PETS MANAGEMENT ==========

// Load all pets
async function loadPets() {
    try {
        const response = await fetch(`${API_BASE}pets.php`);
        const data = await response.json();

        if (data.success) {
            displayPets(data.data);
        } else {
            console.error('Error loading pets:', data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cargar las mascotas. Verifica la conexión con el servidor.');
    }
}

// Display pets in grid
function displayPets(pets) {
    const container = document.getElementById('pets-container');
    const emptyState = document.getElementById('empty-state');

    if (pets.length === 0) {
        container.style.display = 'none';
        emptyState.style.display = 'block';
        return;
    }

    container.style.display = 'grid';
    emptyState.style.display = 'none';

    container.innerHTML = pets.map(pet => `
        <div class="pet-card">
            <div class="pet-header">
                <div>
                    <h3 class="pet-name">${pet.name}</h3>
                    <span class="pet-species">${pet.species}</span>
                </div>
            </div>
            <div class="pet-info">
                ${pet.breed ? `<p>🐕 Raza: ${pet.breed}</p>` : ''}
                ${pet.birth_date ? `<p>🎂 Nacimiento: ${formatDate(pet.birth_date)}</p>` : ''}
                <p>👤 Propietario: ${pet.owner_name}</p>
            </div>
            <div class="pet-actions">
                <button class="btn btn-primary" onclick="openDocumentsModal(${pet.id}, '${pet.name}')">
                    📄 Documentos
                </button>
                <button class="btn btn-secondary" onclick="editPet(${pet.id})">
                    ✏️ Editar
                </button>
                <button class="btn btn-danger" onclick="deletePet(${pet.id})">
                    🗑️
                </button>
            </div>
        </div>
    `).join('');
}

// Open add pet modal
function openAddPetModal() {
    document.getElementById('modal-title').textContent = 'Añadir Mascota';
    document.getElementById('pet-form').reset();
    document.getElementById('pet-id').value = '';
    document.getElementById('pet-modal').classList.add('active');
}

// Close pet modal
function closePetModal() {
    document.getElementById('pet-modal').classList.remove('active');
}

// Edit pet
async function editPet(id) {
    try {
        const response = await fetch(`${API_BASE}pets.php?id=${id}`);
        const data = await response.json();

        if (data.success) {
            const pet = data.data;
            document.getElementById('modal-title').textContent = 'Editar Mascota';
            document.getElementById('pet-id').value = pet.id;
            document.getElementById('pet-name').value = pet.name;
            document.getElementById('pet-species').value = pet.species;
            document.getElementById('pet-breed').value = pet.breed || '';
            document.getElementById('pet-birthdate').value = pet.birth_date || '';
            document.getElementById('pet-owner').value = pet.owner_name;
            document.getElementById('pet-modal').classList.add('active');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cargar los datos de la mascota');
    }
}

// Save pet (create or update)
async function savePet(event) {
    event.preventDefault();

    const id = document.getElementById('pet-id').value;
    const petData = {
        name: document.getElementById('pet-name').value,
        species: document.getElementById('pet-species').value,
        breed: document.getElementById('pet-breed').value,
        birth_date: document.getElementById('pet-birthdate').value,
        owner_name: document.getElementById('pet-owner').value
    };

    try {
        const url = `${API_BASE}pets.php`;
        const method = id ? 'PUT' : 'POST';

        if (id) {
            petData.id = id;
        }

        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(petData)
        });

        const data = await response.json();

        if (data.success) {
            closePetModal();
            loadPets();
            alert(id ? 'Mascota actualizada' : 'Mascota creada');
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al guardar la mascota');
    }
}

// Delete pet
async function deletePet(id) {
    if (!confirm('¿Estás seguro de eliminar esta mascota? Se borrarán todos sus documentos.')) {
        return;
    }

    try {
        const response = await fetch(`${API_BASE}pets.php`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id })
        });

        const data = await response.json();

        if (data.success) {
            loadPets();
            alert('Mascota eliminada');
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al eliminar la mascota');
    }
}

// ========== DOCUMENTS MANAGEMENT ==========

// Open documents modal
function openDocumentsModal(petId, petName) {
    currentPetId = petId;
    document.getElementById('documents-title').textContent = `Documentos de ${petName}`;
    document.getElementById('doc-pet-id').value = petId;
    document.getElementById('documents-modal').classList.add('active');
    loadDocuments(petId);
}

// Close documents modal
function closeDocumentsModal() {
    document.getElementById('documents-modal').classList.remove('active');
    currentPetId = null;
}

// Load documents for a pet
async function loadDocuments(petId) {
    try {
        const response = await fetch(`${API_BASE}documents.php?pet_id=${petId}`);
        const data = await response.json();

        if (data.success) {
            displayDocuments(data.data);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Display documents
function displayDocuments(documents) {
    const container = document.getElementById('documents-list');

    if (documents.length === 0) {
        container.innerHTML = '<p style="grid-column: 1/-1; text-align: center; color: var(--text-secondary);">No hay documentos</p>';
        return;
    }

    container.innerHTML = documents.map(doc => {
        const isPDF = doc.file_path.toLowerCase().endsWith('.pdf');
        const icon = isPDF ? '📄' : '🖼️';

        return `
            <div class="document-card">
                <div class="document-icon">${icon}</div>
                <div class="document-name">${doc.document_type}</div>
                <div style="margin-top: 0.5rem; display: flex; gap: 0.5rem; justify-content: center;">
                    <a href="${doc.file_path}" target="_blank" style="color: var(--primary-color); text-decoration: none;">Ver</a>
                    <button onclick="deleteDocument(${doc.id})" style="background: none; border: none; color: var(--danger-color); cursor: pointer;">🗑️</button>
                </div>
            </div>
        `;
    }).join('');
}

// Upload document
async function uploadDocument(event) {
    event.preventDefault();

    const petId = document.getElementById('doc-pet-id').value;
    const docType = document.getElementById('doc-type').value;
    const fileInput = document.getElementById('doc-file');

    if (!fileInput.files[0]) {
        alert('Selecciona un archivo');
        return;
    }

    const formData = new FormData();
    formData.append('pet_id', petId);
    formData.append('document_type', docType);
    formData.append('file', fileInput.files[0]);

    try {
        const response = await fetch(`${API_BASE}documents.php`, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            fileInput.value = '';
            loadDocuments(petId);
            alert('Documento subido correctamente');
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al subir el documento');
    }
}

// Delete document
async function deleteDocument(id) {
    if (!confirm('¿Eliminar este documento?')) {
        return;
    }

    try {
        // Use POST with action=delete to avoid DELETE method issues on free hosting
        const response = await fetch(`${API_BASE}documents.php?action=delete&id=${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id })
        });

        const data = await response.json();

        if (data.success) {
            loadDocuments(currentPetId);
            alert('Documento eliminado');
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al eliminar el documento');
    }
}

// ========== UTILITIES ==========

function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' });
}

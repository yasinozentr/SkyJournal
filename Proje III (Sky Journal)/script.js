// DOM yüklendiğinde çalış
document.addEventListener('DOMContentLoaded', () => {

    // Gerekli HTML elementlerini seç
    const modalOverlay = document.getElementById('observation-modal-overlay');
    const openModalBtn = document.getElementById('add-observation-btn');
    const closeModalBtn = document.getElementById('close-modal-btn');
    const observationForm = document.getElementById('observation-form');

    // Modalı açan fonksiyon
    const openModal = () => {
        modalOverlay.style.display = 'flex';
    };

    // Modalı kapatan fonksiyon
    const closeModal = () => {
        modalOverlay.style.display = 'none';
    };

    // "+ Gözlem Ekle" butonuna tıklandığında modalı aç
    openModalBtn.addEventListener('click', openModal);

    // "Kapat (X)" butonuna tıklandığında modalı kapat
    closeModalBtn.addEventListener('click', closeModal);

    // Modalın dışındaki koyu alana tıklandığında modalı kapat
    modalOverlay.addEventListener('click', (event) => {
        // Sadece dıştaki overlay'e tıklandığından emin ol
        // (içerideki formun kendisine değil)
        if (event.target === modalOverlay) {
            closeModal();
        }
    });

    // Form gönderildiğinde (şimdilik sadece konsola yazdırıp kapatıyoruz)
    observationForm.addEventListener('submit', (event) => {
        event.preventDefault(); // Sayfanın yeniden yüklenmesini engelle
        
        // Form verilerini al (örnek)
        const title = document.getElementById('title').value;
        const equipment = document.getElementById('equipment').value;
        
        console.log('Yeni Gözlem Gönderildi:');
        console.log('Başlık:', title);
        console.log('Ekipman:', equipment);

        alert('Gözleminiz başarıyla paylaşıldı! (Simülasyon)');
        
        closeModal(); // Formu kapat
        observationForm.reset(); // Formu temizle
    });

});
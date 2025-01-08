document.addEventListener('DOMContentLoaded', () => {
    const galleryImages = document.querySelectorAll('.gallery-image');
    const modalImage = document.getElementById('modalImage');

    galleryImages.forEach(image => {
        image.addEventListener('click', () => {
            const imageSrc = image.getAttribute('src');
            modalImage.setAttribute('src', imageSrc);
        });
    });
});

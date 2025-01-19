// Inicjalizacja mapy
const map = L.map('map').setView([51.505, -0.09], 13); // Środek mapy i poziom zoomu

// Dodanie warstwy mapy (np. OpenStreetMap)
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

// Funkcja do obsługi kliknięcia na mapie
map.on('click', function(e) {
    const lat = e.latlng.lat; // Szerokość geograficzna
    const lng = e.latlng.lng; // Długość geograficzna

    // Wyświetlanie współrzędnych w elemencie <p>
    document.getElementById('coordinates').innerHTML = `Współrzędne: ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
    
    // Dodanie znacznika w miejscu kliknięcia
    L.marker([lat, lng]).addTo(map)
        .bindPopup(`Współrzędne: ${lat.toFixed(5)}, ${lng.toFixed(5)}`)
        .openPopup();
});

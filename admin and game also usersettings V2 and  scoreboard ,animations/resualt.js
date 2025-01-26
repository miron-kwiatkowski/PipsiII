

// Funkcja do wypełnienia tabeli wynikowej
function populateScoreboard() {
    const scoreboard = document.getElementById('scoreboard');
    scoreboard.innerHTML = ""; // Wyczyść istniejące dane

    players.forEach(player => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${player.rank}</td>
            <td>${player.name}</td>
            <td>${player.score}</td>
            <td>${player.time}</td>
        `;
        scoreboard.appendChild(row);

        // Dodaj animację dla każdego wiersza
        row.style.opacity = 0;
        setTimeout(() => {
            row.style.transition = "opacity 0.5s ease-in";
            row.style.opacity = 1;
        }, 200 * player.rank);
    });
}

// Inicjalizacja tabeli wynikowej przy załadowaniu strony
document.addEventListener('DOMContentLoaded', populateScoreboard);

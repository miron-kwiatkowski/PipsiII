// Przykładowe dane graczy
const players = [
    { rank: 1, name: "Alice", score: 150, time: "1:20" },
    { rank: 2, name: "Bob", score: 120, time: "1:45" },
    { rank: 3, name: "Charlie", score: 100, time: "2:10" },
];

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

/* CampusVote scripts. Uses Alpine.js (ballot, live results), Axios (requests) and Chart.js (charts). */

function initials(name) {
    var parts = name.trim().split(/\s+/);
    return (parts[0][0] + (parts.length > 1 ? parts[parts.length - 1][0] : '')).toUpperCase();
}

/* Ballot: choose a candidate, review, then submit */
function ballot() {
    return {
        selected: null, name: '', party: '', color: '#0B3D91',
        open: false, sending: false,
        get initials() { return this.name ? initials(this.name) : ''; },
        pick: function (id, name, party, color) {
            this.selected = id; this.name = name; this.party = party; this.color = color;
        },
        confirm: function () {
            this.sending = true;
            this.$refs.form.submit();   // plain submit, skips the review handler
        }
    };
}

/* Results: chart plus numbers that refresh every 5 seconds while voting is open */
function liveResults(data, live) {
    var chart = null;   // kept outside the reactive data on purpose
    return {
        rows: data.rows, total: data.total, registered: data.registered, pct: data.pct,
        get top() { return Math.max.apply(null, this.rows.map(function (r) { return r.votes; })); },
        initials: initials,
        init: function () {
            var self = this;
            chart = new Chart(this.$refs.chart, {
                type: 'doughnut',
                data: { labels: [], datasets: [{ data: [], backgroundColor: [], borderWidth: 2, borderColor: '#fff' }] },
                options: { maintainAspectRatio: false, cutout: '58%', plugins: { legend: { position: 'bottom' } } }
            });
            this.draw();
            if (live) { setInterval(function () { self.refresh(); }, 5000); }
        },
        draw: function () {
            var rows = this.rows.map(function (r) { return { name: r.name, votes: r.votes, color: r.color }; });
            chart.data.labels = rows.map(function (r) { return r.name; });
            chart.data.datasets[0].data = rows.map(function (r) { return r.votes; });
            chart.data.datasets[0].backgroundColor = rows.map(function (r) { return r.color; });
            chart.update();
        },
        refresh: function () {
            var self = this;
            axios.get('results.php?json=1', { headers: { 'Accept': 'application/json' } })
                .then(function (response) {
                    var d = response.data;
                    self.rows = d.rows; self.total = d.total; self.registered = d.registered; self.pct = d.pct;
                    self.draw();
                })
                .catch(function () { /* keep the numbers on screen, try again next time */ });
        }
    };
}

/* Confirm dialogs on admin forms */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form[data-confirm]').forEach(function (f) {
        f.addEventListener('submit', function (e) {
            if (!window.confirm(f.getAttribute('data-confirm'))) { e.preventDefault(); }
        });
    });
});

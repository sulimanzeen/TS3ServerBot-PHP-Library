(function () {
    var index = window.TS3_SEARCH_INDEX || [];
    var input = document.getElementById('docs-search');
    var box = document.querySelector('.search-results');
    var form = document.querySelector('.search-form');
    var depth = parseInt(document.body.getAttribute('data-depth') || '0', 10);
    var prefix = depth > 0 ? new Array(depth + 1).join('../') : '';
    var active = -1;
    var hits = [];

    function resolve(href) {
        if (!href) {
            return href;
        }
        if (href.indexOf('http') === 0) {
            return href;
        }
        return prefix + href.replace(/^\//, '');
    }

    function normalize(value) {
        return String(value || '').toLowerCase();
    }

    function matches(entry, query) {
        if (!query) {
            return false;
        }
        var hay = normalize(entry.title + ' ' + (entry.summary || '') + ' ' + (entry.keywords || []).join(' '));
        return hay.indexOf(query) !== -1;
    }

    function grouped(list) {
        var order = ['command', 'method', 'parameter', 'option', 'guide'];
        var labels = {
            command: 'Commands',
            method: 'Methods / functions',
            parameter: 'Parameters / args',
            option: 'Options',
            guide: 'Guides'
        };
        var out = [];
        order.forEach(function (type) {
            var items = list.filter(function (item) { return item.type === type; }).slice(0, 8);
            if (items.length) {
                out.push({ type: type, label: labels[type], items: items });
            }
        });
        return out;
    }

    function render(groups) {
        if (!box) {
            return;
        }
        if (!groups.length) {
            box.innerHTML = '<div class="search-hit">No results</div>';
            box.hidden = false;
            return;
        }
        var html = '';
        hits = [];
        groups.forEach(function (group) {
            html += '<div class="search-group">' + group.label + '</div>';
            group.items.forEach(function (item) {
                hits.push(item);
                html += '<a class="search-hit" href="' + resolve(item.href) + '"><span class="badge">' + item.type + '</span>' +
                    escapeHtml(item.title) + '<small>' + escapeHtml(item.summary || '') + '</small></a>';
            });
        });
        box.innerHTML = html;
        box.hidden = false;
        active = -1;
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, function (ch) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[ch];
        });
    }

    function search(query) {
        query = normalize(query).trim();
        if (!query) {
            if (box) {
                box.hidden = true;
                box.innerHTML = '';
            }
            return [];
        }
        var seen = {};
        var list = [];
        index.forEach(function (entry) {
            var key = entry.type + ':' + entry.title + ':' + entry.href;
            if (seen[key] || !matches(entry, query)) {
                return;
            }
            seen[key] = true;
            list.push(entry);
        });
        return grouped(list);
    }

    if (input && box) {
        input.addEventListener('input', function () {
            render(search(input.value));
        });
        input.addEventListener('keydown', function (event) {
            var links = box.querySelectorAll('.search-hit[href]');
            if (event.key === 'Escape') {
                box.hidden = true;
                input.blur();
                return;
            }
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                active = Math.min(active + 1, links.length - 1);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                active = Math.max(active - 1, 0);
            } else if (event.key === 'Enter' && active >= 0 && links[active]) {
                event.preventDefault();
                window.location.href = links[active].getAttribute('href');
                return;
            } else {
                return;
            }
            Array.prototype.forEach.call(links, function (link, i) {
                if (i === active) {
                    link.classList.add('is-active');
                    link.scrollIntoView({ block: 'nearest' });
                } else {
                    link.classList.remove('is-active');
                }
            });
        });
        document.addEventListener('click', function (event) {
            if (form && !form.contains(event.target)) {
                box.hidden = true;
            }
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === '/' && document.activeElement !== input) {
            event.preventDefault();
            if (input) {
                input.focus();
                input.select();
            }
        }
    });

    var toggle = document.querySelector('.menu-toggle');
    if (toggle) {
        toggle.addEventListener('click', function () {
            document.body.classList.toggle('nav-open');
        });
    }

    document.querySelectorAll('[data-table-filter]').forEach(function (bar) {
        var table = document.getElementById('command-table');
        if (!table) {
            return;
        }
        var filterInput = document.getElementById('command-filter');
        function applyFilters() {
            var activeButton = bar.querySelector('button.is-active');
            var filter = activeButton ? activeButton.getAttribute('data-filter') : 'all';
            var query = filterInput ? filterInput.value.toLowerCase().trim() : '';
            Array.prototype.forEach.call(table.querySelectorAll('tbody tr'), function (row) {
                var family = row.getAttribute('data-family');
                var familyOk = filter === 'all' ||
                    (filter === 'both' && family === 'both') ||
                    (filter === 'ts3' && (family === 'ts3' || family === 'both')) ||
                    (filter === 'ts6' && (family === 'ts6' || family === 'both')) ||
                    (filter === 'ts6only' && family === 'ts6');
                var textOk = !query || (row.textContent || '').toLowerCase().indexOf(query) !== -1;
                row.style.display = familyOk && textOk ? '' : 'none';
            });
        }
        bar.addEventListener('click', function (event) {
            var button = event.target.closest('button[data-filter]');
            if (!button) {
                return;
            }
            Array.prototype.forEach.call(bar.querySelectorAll('button[data-filter]'), function (item) {
                item.classList.toggle('is-active', item === button);
            });
            applyFilters();
        });
        if (filterInput) {
            filterInput.addEventListener('input', applyFilters);
        }
    });

    var params = window.location.search.replace(/^\?/, '').split('&');
    var query = '';
    params.forEach(function (part) {
        var bits = part.split('=');
        if (decodeURIComponent(bits[0] || '') === 'q') {
            query = decodeURIComponent((bits[1] || '').replace(/\+/g, ' '));
        }
    });
    var pageResults = document.querySelector('.search-page-results');
    if (query && input) {
        input.value = query;
        if (pageResults) {
            var groups = search(query);
            if (!groups.length) {
                pageResults.innerHTML = '<p class="muted">No results for <code>' + escapeHtml(query) + '</code>.</p>';
            } else {
                var html = '';
                groups.forEach(function (group) {
                    html += '<h2>' + group.label + '</h2><ul>';
                    group.items.forEach(function (item) {
                        html += '<li><a href="' + resolve(item.href) + '"><span class="badge">' + item.type + '</span> ' +
                            escapeHtml(item.title) + '</a> — ' + escapeHtml(item.summary || '') + '</li>';
                    });
                    html += '</ul>';
                });
                pageResults.innerHTML = html;
            }
            if (box) {
                box.hidden = true;
            }
        } else {
            render(search(query));
        }
    }

    if (form) {
        form.addEventListener('submit', function (event) {
            if (!pageResults) {
                event.preventDefault();
                var action = form.getAttribute('action') || 'search.html';
                window.location.href = action + '?q=' + encodeURIComponent(input.value || '');
            }
        });
    }
})();

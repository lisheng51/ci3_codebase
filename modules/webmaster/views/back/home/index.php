<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><?= $title ?></span>
        <button type="button" class="btn btn-primary btn-sm" id="btn_convert_innodb" <?= empty($listdb) ? 'disabled' : '' ?>>
            Alles omzetten naar InnoDB
        </button>
    </div>
    <div class="card-body">
        <div id="innodb_progress_box" class="mb-3 d-none">
            <div class="d-flex justify-content-between mb-1">
                <span id="innodb_progress_label">Bezig...</span>
                <span id="innodb_progress_count">0 / 0</span>
            </div>
            <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="height: 24px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" id="innodb_progress_bar" style="width: 0%">0%</div>
            </div>
            <ul class="list-group list-group-flush mt-3 small" id="innodb_progress_log" style="max-height: 300px; overflow-y: auto;"></ul>
        </div>

        <?php if (empty($listdb)) : ?>
            <div class="alert alert-success mb-0" id="innodb_all_done">Alle tabellen zijn al InnoDB.</div>
        <?php else : ?>
            <div class="table-responsive" id="innodb_table_box">
                <table class="table table-sm table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Tabel</th>
                            <th>Engine</th>
                            <th class="text-end">Rijen (ca.)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listdb as $row) : ?>
                            <tr>
                                <td><?= html_escape($row["name"]) ?></td>
                                <td><span class="badge text-bg-warning"><?= html_escape($row["engine"] ?? 'onbekend') ?></span></td>
                                <td class="text-end"><?= (int)$row["row_count"] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<form id="innodb_csrf_form" class="d-none"><?= add_csrf_value() ?></form>

<script>
    (function() {
        const btn = document.getElementById('btn_convert_innodb');
        const box = document.getElementById('innodb_progress_box');
        const bar = document.getElementById('innodb_progress_bar');
        const label = document.getElementById('innodb_progress_label');
        const count = document.getElementById('innodb_progress_count');
        const log = document.getElementById('innodb_progress_log');

        function post(data) {
            const formData = new FormData(document.getElementById('innodb_csrf_form'));
            Object.keys(data).forEach(key => formData.append(key, data[key]));
            return myfetch({ method: 'POST', body: formData, silent: true });
        }

        function addLog(status, msg) {
            const color = status === 'good' ? 'success' : (status === 'warning' ? 'warning' : 'danger');
            const li = document.createElement('li');
            li.className = 'list-group-item list-group-item-' + color;
            li.textContent = msg;
            log.prepend(li);
        }

        function setProgress(done, total) {
            const percent = total === 0 ? 100 : Math.round(done / total * 100);
            bar.style.width = percent + '%';
            bar.textContent = percent + '%';
            bar.parentElement.setAttribute('aria-valuenow', percent);
            count.textContent = done + ' / ' + total;
        }

        if (!btn) return;
        btn.addEventListener('click', async function() {
            if (!confirm('Alle tabellen omzetten naar InnoDB? Dit kan bij grote tabellen even duren.')) return;

            btn.disabled = true;
            box.classList.remove('d-none');
            log.innerHTML = '';
            bar.className = 'progress-bar progress-bar-striped progress-bar-animated';
            label.textContent = 'Tabellen ophalen...';
            setProgress(0, 0);

            let errors = 0;
            try {
                const list = await post({ action: 'list' });
                const tables = (list && list.tables) ? list.tables : [];
                setProgress(0, tables.length);

                for (let i = 0; i < tables.length; i++) {
                    label.textContent = 'Bezig met: ' + tables[i];
                    try {
                        const json = await post({ action: 'convert', table: tables[i] });
                        if (!json || json.status !== 'good') errors++;
                        addLog(json ? json.status : 'error', json ? json.msg : tables[i] + ': geen antwoord');
                    } catch (err) {
                        errors++;
                        addLog('error', tables[i] + ': ' + err.message);
                    }
                    setProgress(i + 1, tables.length);
                }
            } catch (err) {
                errors++;
                addLog('error', err.message);
            }

            bar.classList.remove('progress-bar-animated', 'progress-bar-striped');
            bar.classList.add(errors ? 'bg-warning' : 'bg-success');
            label.textContent = errors ? 'Klaar met ' + errors + ' fout(en)' : 'Klaar!';
            if (!errors) {
                const tableBox = document.getElementById('innodb_table_box');
                if (tableBox) tableBox.remove();
            } else {
                btn.disabled = false;
            }
        });
    })();
</script>

/**
 * Unified Issue Management Synchronization Client
 * Provides lightweight live polling, real-time status/notes reflection, and modal interactions
 */

(function () {
    'use strict';

    // Configuration
    const POLL_INTERVAL = 4000; // 4 seconds
    let lastDataHash = null;
    let isPolling = false;
    let pollTimer = null;

    // Detect API endpoint relative to current page location
    function getApiUrl() {
        const path = window.location.pathname;
        if (path.includes('/student/') || path.includes('/maintenance/') || path.includes('/admin/')) {
            return '../api/issues.php';
        }
        return 'api/issues.php';
    }

    // Role-dependent helper for status badges
    function renderStatusBadge(status, role) {
        status = status || 'pending';
        const labels = {
            'pending': 'Pending',
            'assigned': 'Assigned',
            'in_progress': 'In Progress',
            'completed': 'Fixed'
        };
        const label = labels[status] || status;

        if (role === 'admin') {
            return `<span class="status-badge status-${status}">${label}</span>`;
        }

        // Student and Maintenance styling
        const styles = {
            'pending': 'background: rgba(245, 158, 11, 0.2); color: #f59e0b;',
            'assigned': 'background: rgba(59, 130, 246, 0.2); color: #3b82f6;',
            'in_progress': 'background: rgba(139, 92, 246, 0.2); color: #8b5cf6;',
            'completed': 'background: rgba(34, 197, 94, 0.2); color: #22c55e;'
        };
        const style = styles[status] || 'background: rgba(107, 114, 128, 0.2); color: #6b7280;';
        return `<span style="display: inline-flex; align-items: center; justify-content: center; padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600; white-space: nowrap; ${style}">${label}</span>`;
    }

    // Update stat numbers with subtle pulse
    function updateStats(stats) {
        if (!stats) return;
        const keys = ['total', 'pending', 'in_progress', 'completed', 'assigned'];
        keys.forEach(key => {
            const els = document.querySelectorAll(`[data-stat="${key}"]`);
            els.forEach(el => {
                const currentVal = el.textContent.trim();
                const newVal = String(stats[key] ?? 0);
                if (currentVal !== newVal) {
                    el.textContent = newVal;
                    el.style.transition = 'transform 0.2s ease, color 0.2s ease';
                    el.style.transform = 'scale(1.15)';
                    setTimeout(() => {
                        el.style.transform = 'scale(1)';
                    }, 250);
                }
            });
        });
    }

    // Modal for Resolution Notes when staff marks an issue Fixed
    function showResolutionNotesModal(issueId, currentNotes, callback) {
        let existingModal = document.getElementById('resolutionNotesModal');
        if (existingModal) {
            existingModal.remove();
        }

        const modalHtml = `
            <div id="resolutionNotesModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.75); display: flex; align-items: center; justify-content: center; z-index: 999999; backdrop-filter: blur(4px); animation: fadeIn 0.2s ease;">
                <div style="background: var(--bg-secondary, #1a2234); border: 1px solid var(--glass-border, rgba(255, 255, 255, 0.1)); border-radius: 14px; width: 90%; max-width: 480px; padding: 1.75rem; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5); color: #fff; font-family: 'Inter', sans-serif;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(34, 197, 94, 0.2); display: flex; align-items: center; justify-content: center; color: #22c55e;">
                                <i class="fas fa-check-circle" style="font-size: 1.2rem;"></i>
                            </div>
                            <h3 style="margin: 0; font-size: 1.2rem; font-weight: 600;">Mark Issue as Fixed</h3>
                        </div>
                        <button id="modalCloseBtn" type="button" style="background: none; border: none; color: #888; font-size: 1.25rem; cursor: pointer; padding: 0.25rem;"><i class="fas fa-times"></i></button>
                    </div>
                    
                    <p style="color: var(--text-muted, #94a3b8); font-size: 0.9rem; margin-bottom: 1rem; line-height: 1.4;">
                        Please provide resolution notes detailing what was repaired, replaced, or checked. This note will appear on the Student and Admin dashboards.
                    </p>

                    <form id="resolutionNotesForm">
                        <div style="margin-bottom: 1.25rem;">
                            <label style="display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 0.5rem; color: #cbd5e1;">Resolution Details / Notes *</label>
                            <textarea id="resolutionNotesInput" required rows="4" placeholder="e.g., Replaced broken door handle with new brass lockset. Tested lock functionality." style="width: 100%; box-sizing: border-box; background: rgba(0, 0, 0, 0.25); border: 1px solid var(--glass-border, rgba(255, 255, 255, 0.15)); border-radius: 8px; padding: 0.75rem; color: #fff; font-family: inherit; font-size: 0.9rem; resize: vertical;">${currentNotes ? currentNotes : ''}</textarea>
                        </div>
                        <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                            <button type="button" id="modalCancelBtn" style="padding: 0.6rem 1.2rem; background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.12); color: #cbd5e1; border-radius: 8px; cursor: pointer; font-size: 0.85rem; font-weight: 500;">Cancel</button>
                            <button type="submit" id="modalSubmitBtn" style="padding: 0.6rem 1.25rem; background: #22c55e; border: none; color: #fff; border-radius: 8px; cursor: pointer; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-check"></i> Complete & Mark Fixed
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);

        const modal = document.getElementById('resolutionNotesModal');
        const form = document.getElementById('resolutionNotesForm');
        const input = document.getElementById('resolutionNotesInput');
        const closeBtn = document.getElementById('modalCloseBtn');
        const cancelBtn = document.getElementById('modalCancelBtn');

        input.focus();

        const cleanup = () => {
            if (modal) modal.remove();
        };

        closeBtn.onclick = cleanup;
        cancelBtn.onclick = cleanup;
        modal.onclick = (e) => {
            if (e.target === modal) cleanup();
        };

        form.onsubmit = (e) => {
            e.preventDefault();
            const notes = input.value.trim();
            if (!notes) {
                alert('Please enter resolution notes describing the repair.');
                return;
            }
            cleanup();
            callback(notes);
        };
    }

    // Submit an action via AJAX
    function submitIssueAction(issueId, action, extraData, onSuccess) {
        const payload = Object.assign({
            action: action,
            issue_id: issueId
        }, extraData || {});

        fetch(getApiUrl(), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (data.stats) updateStats(data.stats);
                if (typeof onSuccess === 'function') {
                    onSuccess(data);
                }
                // Trigger immediate sync
                pollIssues(true);
            } else {
                alert(data.message || 'Action failed.');
            }
        })
        .catch(err => {
            console.error('Issue action failed:', err);
            alert('A network error occurred. Please try again.');
        });
    }

    // Update existing row elements in table
    function syncIssueRow(issue, staffLookup, role) {
        const row = document.querySelector(`tr[data-issue-id="${issue.id}"]`);
        if (!row) return;

        // Status badge update
        const statusCell = row.querySelector('[data-field="status"]');
        if (statusCell) {
            statusCell.innerHTML = renderStatusBadge(issue.status, role);
        }

        // Assigned staff update
        const staffCell = row.querySelector('[data-field="assigned_to"]');
        if (staffCell) {
            if (issue.assigned_to && staffLookup && staffLookup[issue.assigned_to]) {
                const sName = staffLookup[issue.assigned_to].name;
                staffCell.innerHTML = `<span style="display: inline-flex; align-items: center; gap: 0.35rem; color: #60a5fa;"><i class="fas fa-user-cog" style="font-size: 0.75rem;"></i> ${sName}</span>`;
            } else {
                staffCell.innerHTML = `<span style="color: var(--text-muted, #94a3b8); font-size: 0.85rem;">— Unassigned</span>`;
            }
        }

        // Resolution notes update
        const notesCell = row.querySelector('[data-field="notes"]');
        if (notesCell) {
            if (issue.maintenance_notes && issue.maintenance_notes.trim() !== '') {
                notesCell.innerHTML = `
                    <div style="background: rgba(34, 197, 94, 0.1); border-left: 3px solid #22c55e; padding: 0.4rem 0.6rem; border-radius: 4px; font-size: 0.8rem; color: #86efac; margin-top: 0.25rem;">
                        <strong>Resolution:</strong> ${escapeHtml(issue.maintenance_notes)}
                    </div>`;
            } else {
                notesCell.innerHTML = `<span style="color: var(--text-muted, #94a3b8); font-size: 0.8rem; font-style: italic;">No notes yet</span>`;
            }
        }

        // Action buttons update for maintenance staff view
        const actionsCell = row.querySelector('[data-field="actions"]');
        if (actionsCell && role === 'staff') {
            let actionsHtml = `
                <div style="display: flex; gap: 0.35rem; align-items: center;">
                    <a href="issue-details.php?id=${encodeURIComponent(issue.id)}" style="display: inline-flex; align-items: center; justify-content: center; gap: 0.25rem; padding: 0.25rem 0.5rem; background: rgba(34, 197, 94, 0.2); color: #22c55e; text-decoration: none; border: 1px solid rgba(34, 197, 94, 0.3); border-radius: 4px; font-size: 0.7rem; font-weight: 500; white-space: nowrap;">
                        View <i class="fas fa-arrow-right" style="font-size: 0.55rem;"></i>
                    </a>
            `;

            if (issue.status === 'pending') {
                actionsHtml += `
                    <button type="button" class="btn-start-working" data-id="${escapeHtml(issue.id)}" style="display: inline-flex; align-items: center; justify-content: center; gap: 0.25rem; padding: 0.25rem 0.5rem; background: rgba(59, 130, 246, 0.2); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 4px; font-size: 0.7rem; font-weight: 500; cursor: pointer; white-space: nowrap;">
                        <i class="fas fa-play" style="font-size: 0.55rem;"></i> Start Working
                    </button>
                `;
            } else if (issue.status === 'in_progress') {
                actionsHtml += `
                    <button type="button" class="btn-mark-fixed" data-id="${escapeHtml(issue.id)}" data-notes="${escapeHtml(issue.maintenance_notes || '')}" style="display: inline-flex; align-items: center; justify-content: center; gap: 0.25rem; padding: 0.25rem 0.5rem; background: rgba(34, 197, 94, 0.2); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.3); border-radius: 4px; font-size: 0.7rem; font-weight: 500; cursor: pointer; white-space: nowrap;">
                        <i class="fas fa-check" style="font-size: 0.55rem;"></i> Mark as Fixed
                    </button>
                `;
            } else if (issue.status === 'completed') {
                actionsHtml += `
                    <span style="display: inline-flex; align-items: center; justify-content: center; gap: 0.25rem; padding: 0.25rem 0.5rem; background: rgba(34, 197, 94, 0.2); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.3); border-radius: 4px; font-size: 0.7rem; font-weight: 500; white-space: nowrap;">
                        <i class="fas fa-check-circle" style="font-size: 0.55rem;"></i> Fixed
                    </span>
                `;
            }

            actionsHtml += `</div>`;
            actionsCell.innerHTML = actionsHtml;
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Main Polling routine
    function pollIssues(force = false) {
        if (isPolling && !force) return;
        isPolling = true;

        fetch(getApiUrl(), {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        })
        .then(res => {
            if (!res.ok) throw new Error('API request failed with status ' + res.status);
            return res.json();
        })
        .then(data => {
            isPolling = false;
            if (!data.success) return;

            // Update stat cards
            if (data.stats) {
                updateStats(data.stats);
            }

            // If data hash changed or force sync, update issue rows
            if (force || data.data_hash !== lastDataHash) {
                lastDataHash = data.data_hash;

                if (Array.isArray(data.issues)) {
                    data.issues.forEach(issue => {
                        syncIssueRow(issue, data.staff_lookup, data.role);
                    });

                    // Check if there are newly added issues not yet present in tbody
                    const tbody = document.getElementById('issues-table-body') || document.querySelector('tbody[data-sync-container="issues"]');
                    if (tbody && data.issues.length > 0) {
                        const existingIds = new Set(
                            Array.from(tbody.querySelectorAll('tr[data-issue-id]')).map(tr => tr.getAttribute('data-issue-id'))
                        );

                        // If any issue in data.issues is not in existingIds, refresh table or prepend row
                        const hasMissing = data.issues.some(iss => !existingIds.has(iss.id));
                        if (hasMissing) {
                            // If table has empty state message, reload page or dynamically render
                            const emptyPlaceholder = tbody.querySelector('.no-issues-row');
                            if (emptyPlaceholder) {
                                window.location.reload();
                            } else {
                                // Rerender or reload cleanly
                                window.location.reload();
                            }
                        }
                    }
                }
            }
        })
        .catch(err => {
            isPolling = false;
            console.debug('Live sync poll notice:', err.message);
        });
    }

    // Setup Event Listeners for action buttons
    function bindActionDelegation() {
        document.addEventListener('click', function (e) {
            // Start Working button
            const startBtn = e.target.closest('.btn-start-working');
            if (startBtn) {
                e.preventDefault();
                const issueId = startBtn.getAttribute('data-id');
                if (!issueId) return;

                if (confirm('Start working on this maintenance issue?')) {
                    startBtn.disabled = true;
                    startBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Starting...';
                    submitIssueAction(issueId, 'start_working', {}, () => {
                        // Optimistically update
                        const row = document.querySelector(`tr[data-issue-id="${issueId}"]`);
                        if (row) {
                            const statusCell = row.querySelector('[data-field="status"]');
                            if (statusCell) {
                                statusCell.innerHTML = renderStatusBadge('in_progress', 'staff');
                            }
                        }
                    });
                }
                return;
            }

            // Mark as Fixed button
            const fixBtn = e.target.closest('.btn-mark-fixed');
            if (fixBtn) {
                e.preventDefault();
                const issueId = fixBtn.getAttribute('data-id');
                const existingNotes = fixBtn.getAttribute('data-notes') || '';
                if (!issueId) return;

                showResolutionNotesModal(issueId, existingNotes, function (notes) {
                    fixBtn.disabled = true;
                    fixBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
                    submitIssueAction(issueId, 'mark_fixed', { maintenance_notes: notes }, () => {
                        const row = document.querySelector(`tr[data-issue-id="${issueId}"]`);
                        if (row) {
                            const statusCell = row.querySelector('[data-field="status"]');
                            if (statusCell) {
                                statusCell.innerHTML = renderStatusBadge('completed', 'staff');
                            }
                            const notesCell = row.querySelector('[data-field="notes"]');
                            if (notesCell) {
                                notesCell.innerHTML = `
                                    <div style="background: rgba(34, 197, 94, 0.1); border-left: 3px solid #22c55e; padding: 0.4rem 0.6rem; border-radius: 4px; font-size: 0.8rem; color: #86efac; margin-top: 0.25rem;">
                                        <strong>Resolution:</strong> ${escapeHtml(notes)}
                                    </div>`;
                            }
                        }
                    });
                });
                return;
            }
        });
    }

    // Initialize Poller and Visibility Handlers
    function init() {
        bindActionDelegation();

        // Initial poll
        pollIssues(true);

        // Schedule periodic polling
        pollTimer = setInterval(() => {
            if (document.visibilityState === 'visible') {
                pollIssues(false);
            }
        }, POLL_INTERVAL);

        // Resume immediately on tab focus/visibility
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                pollIssues(true);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

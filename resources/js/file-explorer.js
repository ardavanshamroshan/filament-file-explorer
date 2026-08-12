function qfCtxFlyout(openDelay = 160, closeDelay = 100) {
            return {
                open: false,
                _timer: null,
                show() {
                    clearTimeout(this._timer);
                    this._timer = setTimeout(() => { this.open = true; }, openDelay);
                },
                hide() {
                    clearTimeout(this._timer);
                    this._timer = setTimeout(() => { this.open = false; }, closeDelay);
                },
            };
        }

        // Keep Livewire $wire outside Alpine reactive stores — wrapping the proxy breaks method calls.
        let feWireRef = null;

        function setFeWire(wire) {
            if (wire) {
                feWireRef = wire;
            }
        }

        function callFeWire(method, ...args) {
            const wire = feWireRef;
            if (!wire) return;

            const direct = wire[method];
            if (typeof direct === 'function') {
                return direct.apply(wire, args);
            }

            if (typeof wire.$call === 'function') {
                return wire.$call(method, ...args);
            }

            if (typeof wire.call === 'function') {
                return wire.call(method, ...args);
            }
        }

        function registerQfSelStore() {
            if (typeof Alpine === 'undefined') return;
            if (Alpine.store('feDrag')?.pointerDown) {
                return;
            }
            Alpine.store('feSel', {
                folders: [],
                files: [],
                marqueeFolders: [],
                marqueeFiles: [],
                _syncTimer: null,
                setWire(wire) {
                    setFeWire(wire);
                },
                replace(folders, files) {
                    this.folders = (folders || []).map(Number);
                    this.files = (files || []).map(Number);
                },
                setMarquee(folders, files) {
                    this.marqueeFolders = (folders || []).map(Number);
                    this.marqueeFiles = (files || []).map(Number);
                },
                clearMarquee() {
                    this.marqueeFolders = [];
                    this.marqueeFiles = [];
                },
                hasFolder(id) {
                    return this.folders.includes(Number(id));
                },
                hasFile(id) {
                    return this.files.includes(Number(id));
                },
                inMarqueeFolder(id) {
                    return this.marqueeFolders.includes(Number(id));
                },
                inMarqueeFile(id) {
                    return this.marqueeFiles.includes(Number(id));
                },
                count() {
                    return this.folders.length + this.files.length;
                },
                toggle(type, id, multi) {
                    id = Number(id);
                    let folders = [...this.folders];
                    let files = [...this.files];

                    if (!multi) {
                        folders = type === 'folder' ? [id] : [];
                        files = type === 'file' ? [id] : [];
                    } else if (type === 'folder') {
                        folders = folders.includes(id)
                            ? folders.filter((x) => x !== id)
                            : [...folders, id];
                    } else {
                        files = files.includes(id)
                            ? files.filter((x) => x !== id)
                            : [...files, id];
                    }

                    this.folders = folders;
                    this.files = files;
                    this.queueSync();
                },
                clear(opts = {}) {
                    const sync = opts.sync !== false;
                    this.folders = [];
                    this.files = [];
                    this.clearMarquee();
                    if (this._syncTimer) clearTimeout(this._syncTimer);
                    this._syncTimer = null;
                    if (sync) callFeWire('clearSelection');
                },
                flushSync() {
                    if (this._syncTimer) clearTimeout(this._syncTimer);
                    this._syncTimer = null;
                    callFeWire('setSelection', [...this.folders], [...this.files]);
                },
                queueSync() {
                    if (this._syncTimer) clearTimeout(this._syncTimer);
                    this._syncTimer = setTimeout(() => {
                        this._syncTimer = null;
                        callFeWire('setSelection', [...this.folders], [...this.files]);
                    }, 40);
                },
            });

            Alpine.store('feDrag', {
                active: false,
                moved: false,
                suppressClick: false,
                folders: [],
                files: [],
                label: '',
                dropTargetId: null,
                ghost: null,
                startX: 0,
                startY: 0,
                _onMove: null,
                _onUp: null,
                abilities: {},

                prepareSelection(type, id) {
                    const sel = Alpine.store('feSel');
                    id = Number(id);
                    if (type === 'folder' && !sel.hasFolder(id)) {
                        sel.replace([id], []);
                    } else if (type === 'file' && !sel.hasFile(id)) {
                        sel.replace([], [id]);
                    }
                    this.folders = [...sel.folders];
                    this.files = [...sel.files];
                },

                pointerDown(event, type, id, label, wire) {
                    if (event.button !== 0) return;
                    if (event.target.closest('input, textarea, button, a, .fe-rename-input')) return;
                    // Keep default so double-click still works; stop bubble so marquee does not start
                    event.stopPropagation();

                    setFeWire(wire);
                    this.label = label || 'item';
                    this.prepareSelection(type, id);
                    this.startX = event.clientX;
                    this.startY = event.clientY;
                    this.active = false;
                    this.moved = false;
                    this.dropTargetId = null;

                    this._onMove = (e) => this.pointerMove(e);
                    this._onUp = (e) => this.pointerUp(e);
                    window.addEventListener('pointermove', this._onMove, true);
                    window.addEventListener('pointerup', this._onUp, true);
                    window.addEventListener('pointercancel', this._onUp, true);
                },

                pointerMove(event) {
                    const canMove = !!(this.abilities.move || this.abilities.copy);
                    if (!canMove) return;

                    const dx = event.clientX - this.startX;
                    const dy = event.clientY - this.startY;

                    if (!this.active && (Math.abs(dx) > 5 || Math.abs(dy) > 5)) {
                        this.active = true;
                        this.moved = true;
                        this.suppressClick = true;
                        this.showGhost();
                        document.body.classList.add('fe-is-dragging');
                        window.dispatchEvent(new CustomEvent('fe-item-drag-start'));
                    }

                    if (!this.active) return;

                    event.preventDefault();
                    this.moveGhost(event.clientX, event.clientY);
                    this.updateDropTarget(event.clientX, event.clientY);
                },

                pointerUp(event) {
                    window.removeEventListener('pointermove', this._onMove, true);
                    window.removeEventListener('pointerup', this._onUp, true);
                    window.removeEventListener('pointercancel', this._onUp, true);
                    this._onMove = null;
                    this._onUp = null;

                    if (this.active && this.dropTargetId !== null) {
                        const targetId = Number(this.dropTargetId);
                        const folders = this.folders.filter((id) => id !== targetId);
                        const files = [...this.files];

                        if (folders.length || files.length) {
                            const copy = event.altKey || event.ctrlKey;
                            if (copy && this.abilities.copy) {
                                callFeWire('copyItemsToFolder', targetId, folders, files);
                                Alpine.store('feSel').clear({ sync: false });
                            } else if (!copy && this.abilities.move) {
                                callFeWire('moveItemsToFolder', targetId, folders, files);
                                Alpine.store('feSel').clear({ sync: false });
                            }
                        }
                    }

                    this.cleanup();
                },

                showGhost() {
                    this.hideGhost();
                    const count = this.folders.length + this.files.length;
                    const ghost = document.createElement('div');
                    ghost.className = 'fe-drag-ghost';
                    ghost.textContent = count > 1 ? (count + ' items') : this.label;
                    document.body.appendChild(ghost);
                    this.ghost = ghost;
                    this.moveGhost(this.startX, this.startY);
                },

                moveGhost(x, y) {
                    if (!this.ghost) return;
                    this.ghost.style.left = (x + 12) + 'px';
                    this.ghost.style.top = (y + 12) + 'px';
                },

                hideGhost() {
                    if (this.ghost) {
                        this.ghost.remove();
                        this.ghost = null;
                    }
                },

                updateDropTarget(x, y) {
                    this.ghost && (this.ghost.style.pointerEvents = 'none');
                    const el = document.elementFromPoint(x, y);
                    const drop = el?.closest?.('[data-fe-drop-folder]');
                    const id = drop ? Number(drop.getAttribute('data-fe-drop-folder')) : null;

                    // Do not allow drop onto a selected dragged folder only-item
                    if (id !== null && this.folders.includes(id) && this.files.length === 0 && this.folders.length === 1) {
                        this.dropTargetId = null;
                        return;
                    }

                    this.dropTargetId = Number.isFinite(id) ? id : null;
                },

                cleanup() {
                    this.hideGhost();
                    document.body.classList.remove('fe-is-dragging');
                    this.active = false;
                    this.dropTargetId = null;
                    this.folders = [];
                    this.files = [];
                    window.dispatchEvent(new CustomEvent('fe-item-drag-end'));
                },

                consumeClickSuppression() {
                    if (!this.suppressClick) return false;
                    this.suppressClick = false;
                    return true;
                },

                dropFilesOnFolder(event, targetFolderId, wire) {
                    event.preventDefault();
                    event.stopPropagation();
                    if (!this.abilities.upload) return;
                    if (!event.dataTransfer?.files?.length) return;
                    setFeWire(wire);
                    callFeWire('prepareUploadToFolder', targetFolderId);
                    window.dispatchEvent(new CustomEvent('fe-upload-files', {
                        detail: { files: event.dataTransfer.files },
                    }));
                },
            });
        }

        function registerQfUploadStore() {
            if (Alpine.store('feUpload')) return;
            Alpine.store('feUpload', {
                visible: false,
                progress: 0,
                status: 'idle',
                label: 'Uploading…',
                hideTimer: null,
                start() {
                    if (this.hideTimer) clearTimeout(this.hideTimer);
                    this.visible = true;
                    this.progress = 0;
                    this.status = 'uploading';
                    this.label = 'Uploading…';
                    this.hideTimer = null;
                },
                progressTo(p) {
                    this.progress = p;
                },
                finish() {
                    this.status = 'done';
                    this.progress = 100;
                    this.label = 'Upload complete';
                    this.scheduleHide(900);
                },
                error(label = 'Upload failed') {
                    this.status = 'error';
                    this.progress = 100;
                    this.label = label;
                    this.scheduleHide(2200);
                },
                cancel() {
                    this.status = 'cancelled';
                    this.progress = 100;
                    this.label = 'Upload cancelled';
                    this.scheduleHide(1800);
                },
                settled() {
                    if (this.status === 'uploading') {
                        this.finish();
                        return;
                    }
                    if (this.visible) {
                        this.scheduleHide(400);
                    }
                },
                scheduleHide(ms) {
                    if (this.hideTimer) clearTimeout(this.hideTimer);
                    this.hideTimer = setTimeout(() => this.hide(), ms);
                },
                hide() {
                    if (this.hideTimer) clearTimeout(this.hideTimer);
                    this.hideTimer = null;
                    this.visible = false;
                    this.status = 'idle';
                    this.progress = 0;
                    this.label = 'Uploading…';
                },
            });
        }

        function registerQfUiStore() {
            if (Alpine.store('feUi')) {
                return;
            }
            Alpine.store('feUi', {
                sidebarOpen: true,
                sideExpanded: {},
                isOpen(id, fallback) {
                    const v = this.sideExpanded[id];
                    return v === undefined ? !!fallback : !!v;
                },
                toggle(id, fallback) {
                    this.sideExpanded = {
                        ...this.sideExpanded,
                        [id]: !this.isOpen(id, fallback),
                    };
                },
            });
        }

        document.addEventListener('alpine:init', () => {
            registerQfSelStore();
            registerQfUiStore();
            registerQfUploadStore();
        });
        if (window.Alpine) {
            registerQfSelStore();
            registerQfUiStore();
            registerQfUploadStore();
        }

        function FileExplorerUi(config) {
            return {
                uploading: false,
                progress: 0,
                dropingFile: false,
                isDrawing: false,
                startX: 0,
                startY: 0,
                drawnArea: null,
                hoveredElements: new Set(),
                wasDrawing: false,
                isDraggingItems: false,
                rootFolderId: config.rootFolderId,
                scopeKey: config.scopeKey,
                abilities: config.abilities || {},
                mediaUrlBase: config.mediaUrlBase || '/file-explorer/media',
                translations: config.translations || {},
                ctx: { open: false, type: 'empty', id: null, name: '', x: 0, y: 0, canDelete: true, deleteHint: '' },

                init() {
                    registerQfUiStore();
                    registerQfSelStore();
                    registerQfUploadStore();
                    Alpine.store('feSel').setWire(this.$wire);
                    Alpine.store('feDrag').abilities = this.abilities;
                    Alpine.store('feSel').replace(
                        config.selectedFolders || [],
                        config.selectedFiles || []
                    );
                    this.$watch('$wire.selectedFolders', (v) => {
                        const local = Alpine.store('feSel').folders.join(',');
                        const server = (v || []).map(Number).join(',');
                        const localFiles = Alpine.store('feSel').files.join(',');
                        const serverFiles = (this.$wire.selectedFiles || []).map(Number).join(',');
                        if (local === server && localFiles === serverFiles) {
                            return;
                        }
                        if (((v || []).length + (this.$wire.selectedFiles || []).length) === 0) {
                            Alpine.store('feSel').replace([], []);
                            return;
                        }
                        if (Alpine.store('feSel')._syncTimer) {
                            return;
                        }
                        Alpine.store('feSel').replace(v, this.$wire.selectedFiles);
                    });
                },

                onUploadStart() {
                    Alpine.store('feUpload').start();
                    this.uploading = true;
                },
                onUploadProgress(p) {
                    Alpine.store('feUpload').progressTo(p);
                    this.progress = p;
                },
                onUploadFinish() {
                    this.uploading = false;
                    Alpine.store('feUpload').finish();
                },
                onUploadError() {
                    this.uploading = false;
                    Alpine.store('feUpload').error();
                },
                onUploadCancel() {
                    this.uploading = false;
                    Alpine.store('feUpload').cancel();
                },
                onUploadSettled() {
                    this.uploading = false;
                    Alpine.store('feUpload').settled();
                },
                confirmDeleteSelected() {
                    const folders = [...Alpine.store('feSel').folders];
                    const files = [...Alpine.store('feSel').files];
                    if (!folders.length && !files.length) return;
                    if (!confirm(this.translations?.js?.confirm_delete_selected || 'Delete selected items?')) return;
                    if (Alpine.store('feSel')._syncTimer) clearTimeout(Alpine.store('feSel')._syncTimer);
                    Alpine.store('feSel')._syncTimer = null;
                    Alpine.store('feSel').clearMarquee();
                    this.$wire.deleteSelected(folders, files);
                    Alpine.store('feSel').replace([], []);
                },
                async openContext(detail) {
                    this.positionMenu(detail.x, detail.y);
                    this.ctx = {
                        open: true,
                        type: detail.type,
                        id: detail.id,
                        name: detail.name || '',
                        x: this.ctx.x,
                        y: this.ctx.y,
                        canDelete: true,
                        deleteHint: '',
                    };
                    if (detail.type === 'file' || detail.type === 'folder') {
                        try {
                            const state = await this.$wire.getDeleteState(detail.type, detail.id);
                            this.ctx.canDelete = !!state.allowed;
                            this.ctx.deleteHint = state.hint || state.reason || '';
                        } catch (e) {
                            this.ctx.canDelete = false;
                            this.ctx.deleteHint = this.translations?.js?.delete_not_allowed || 'Delete not allowed';
                        }
                    }
                },
                openEmptyContext(event) {
                    if (event.target.closest('.folder, .file, [data-fe-type]')) return;
                    this.positionMenu(event.clientX, event.clientY);
                    this.ctx = { open: true, type: 'empty', id: null, name: '', x: this.ctx.x, y: this.ctx.y, canDelete: false, deleteHint: '' };
                },
                async toolbarRename() {
                    const sel = Alpine.store('feSel');
                    await this.$wire.setSelection([...sel.folders], [...sel.files]);
                    if (sel.folders.length === 1 && sel.files.length === 0) {
                        await this.$wire.startRename('folder', sel.folders[0]);
                    } else if (sel.files.length === 1 && sel.folders.length === 0) {
                        await this.$wire.startRename('file', sel.files[0]);
                    }
                },
                async toolbarCopy() {
                    const sel = Alpine.store('feSel');
                    await this.$wire.setSelection([...sel.folders], [...sel.files]);
                    await this.$wire.copySelection();
                },
                async toolbarInfo() {
                    const sel = Alpine.store('feSel');
                    await this.$wire.setSelection([...sel.folders], [...sel.files]);
                    if (sel.folders.length === 1 && sel.files.length === 0) {
                        await this.$wire.showInfo('folder', sel.folders[0]);
                    } else if (sel.files.length === 1 && sel.folders.length === 0) {
                        await this.$wire.showInfo('file', sel.files[0]);
                    } else {
                        await this.$wire.showInfo();
                    }
                },
                toolbarDownload() {
                    const sel = Alpine.store('feSel');
                    if (sel.folders.length === 1 && sel.files.length === 0) {
                        window.location.href = this.folderZipUrl(sel.folders[0]);
                        return;
                    }
                    if (sel.files.length === 1 && sel.folders.length === 0) {
                        window.location.href = this.fileUrl(sel.files[0], true);
                        return;
                    }
                    if (sel.folders.length === 1) {
                        window.location.href = this.folderZipUrl(sel.folders[0]);
                        return;
                    }
                    if (sel.files.length >= 1) {
                        window.location.href = this.mediaZipUrl(sel.files[0]);
                    }
                },

                fileUrl(id, download) {
                    const base = `${this.mediaUrlBase}/${this.scopeKey}/files/${id}`;
                    return download ? `${base}?download=1` : base;
                },
                mediaZipUrl(id) {
                    return `${this.mediaUrlBase}/${this.scopeKey}/files/${id}/zip`;
                },
                folderZipUrl(id) {
                    return `${this.mediaUrlBase}/${this.scopeKey}/folders/${id}/zip?root=${this.rootFolderId}`;
                },
                positionMenu(x, y) {
                    const pad = 8;
                    const w = 240;
                    const h = 360;
                    this.ctx.x = Math.min(x, window.innerWidth - w - pad);
                    this.ctx.y = Math.min(y, window.innerHeight - h - pad);
                    if (this.ctx.x < pad) this.ctx.x = pad;
                    if (this.ctx.y < pad) this.ctx.y = pad;
                },
                closeContext() {
                    this.ctx.open = false;
                },
                run(fn) {
                    this.closeContext();
                    fn();
                },

                localPoint(clientX, clientY) {
                    const container = document.getElementById('folder-container');
                    const rect = container.getBoundingClientRect();
                    return {
                        x: clientX - rect.left + container.scrollLeft,
                        y: clientY - rect.top + container.scrollTop,
                    };
                },
                bindMarqueeListeners() {
                    this._onMarqueeMove = (event) => this.draw(event);
                    this._onMarqueeUp = (event) => this.stopDrawing(event);
                    window.addEventListener('mousemove', this._onMarqueeMove, true);
                    window.addEventListener('mouseup', this._onMarqueeUp, true);
                },
                unbindMarqueeListeners() {
                    if (this._onMarqueeMove) window.removeEventListener('mousemove', this._onMarqueeMove, true);
                    if (this._onMarqueeUp) window.removeEventListener('mouseup', this._onMarqueeUp, true);
                    this._onMarqueeMove = null;
                    this._onMarqueeUp = null;
                },
                initiateDrawing(event) {
                    if (this.isDraggingItems || Alpine.store('feDrag')?.active) return;
                    if (event.button !== 0) return;
                    if (event.target.closest('.folder, .file, input, button, a, .fe-caption, .fe-rename-input, .fe-side-node')) return;

                    // Threshold: wait for move before clearing selection / showing box
                    const startPt = this.localPoint(event.clientX, event.clientY);
                    this.startX = startPt.x;
                    this.startY = startPt.y;
                    this._marqueePending = true;
                    this.isDrawing = false;
                    this.drawnArea = null;

                    const onMove = (e) => {
                        if (!this._marqueePending && !this.isDrawing) return;
                        const pt = this.localPoint(e.clientX, e.clientY);
                        const dx = Math.abs(pt.x - this.startX);
                        const dy = Math.abs(pt.y - this.startY);
                        if (!this.isDrawing && (dx > 4 || dy > 4)) {
                            this._marqueePending = false;
                            this.isDrawing = true;
                            Alpine.store('feSel').clear({ sync: false });
                            this.drawnArea = { left: this.startX, top: this.startY, width: 0, height: 0 };
                        }
                        if (this.isDrawing) {
                            e.preventDefault();
                            this.draw(e);
                        }
                    };
                    const onUp = () => {
                        window.removeEventListener('mousemove', onMove, true);
                        window.removeEventListener('mouseup', onUp, true);
                        this._marqueePending = false;
                        this.stopDrawing();
                    };
                    window.addEventListener('mousemove', onMove, true);
                    window.addEventListener('mouseup', onUp, true);
                    this._onMarqueeMove = onMove;
                    this._onMarqueeUp = onUp;
                },
                draw(event) {
                    if (!this.isDrawing) return;
                    const pt = this.localPoint(event.clientX, event.clientY);
                    const width = pt.x - this.startX;
                    const height = pt.y - this.startY;
                    this.drawnArea = {
                        width: Math.abs(width),
                        height: Math.abs(height),
                        left: width < 0 ? pt.x : this.startX,
                        top: height < 0 ? pt.y : this.startY,
                    };
                    this.updateHoveredElements();
                },
                stopDrawing() {
                    this.unbindMarqueeListeners();
                    if (!this.isDrawing) {
                        this.drawnArea = null;
                        return;
                    }
                    const meaningful = this.drawnArea && (this.drawnArea.width > 3 || this.drawnArea.height > 3);
                    if (meaningful) {
                        this.wasDrawing = true;
                        this.selectElementsWithinDrawnArea();
                        const sel = Alpine.store('feSel');
                        if (!sel.folders.length && !sel.files.length) {
                            sel.clear({ sync: true });
                        }
                    }
                    Alpine.store('feSel').clearMarquee();
                    this.isDrawing = false;
                    this.drawnArea = null;
                },
                updateHoveredElements() {
                    const container = document.getElementById('folder-container');
                    if (!container || !this.drawnArea) return;
                    const drawnRect = {
                        left: this.drawnArea.left,
                        top: this.drawnArea.top,
                        right: this.drawnArea.left + this.drawnArea.width,
                        bottom: this.drawnArea.top + this.drawnArea.height,
                    };
                    const folders = [];
                    const files = [];
                    const crect = container.getBoundingClientRect();
                    container.querySelectorAll('.folder, .file').forEach((element) => {
                        const rect = element.getBoundingClientRect();
                        const elementRect = {
                            left: rect.left - crect.left + container.scrollLeft,
                            top: rect.top - crect.top + container.scrollTop,
                            right: rect.right - crect.left + container.scrollLeft,
                            bottom: rect.bottom - crect.top + container.scrollTop,
                        };
                        if (this.isElementWithinDrawnArea(drawnRect, elementRect)) {
                            const id = parseInt(element.getAttribute('data-id'), 10);
                            if (element.classList.contains('folder')) folders.push(id);
                            else files.push(id);
                        }
                    });
                    Alpine.store('feSel').setMarquee(folders, files);
                },
                selectElementsWithinDrawnArea() {
                    const folders = [...Alpine.store('feSel').marqueeFolders];
                    const files = [...Alpine.store('feSel').marqueeFiles];
                    if (folders.length > 0 || files.length > 0) {
                        Alpine.store('feSel').replace(folders, files);
                        if (Alpine.store('feSel')._syncTimer) clearTimeout(Alpine.store('feSel')._syncTimer);
                        Alpine.store('feSel')._syncTimer = null;
                        this.$wire.setSelection(folders, files);
                    }
                },
                isElementWithinDrawnArea(drawnRect, elementRect) {
                    const margin = 2;
                    return !(drawnRect.left > elementRect.right + margin ||
                             drawnRect.right < elementRect.left - margin ||
                             drawnRect.top > elementRect.bottom + margin ||
                             drawnRect.bottom < elementRect.top - margin);
                },

                handleContainerClick(event) {
                    if (!this.wasDrawing && event.target === event.currentTarget) {
                        Alpine.store('feSel').clear();
                    }
                    this.wasDrawing = false;
                },
                handleFileDrop(e) {
                    if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                        this.uploadDroppedFiles(e.dataTransfer.files);
                    }
                },
                onItemDragStart() {
                    this.isDraggingItems = true;
                    this._marqueePending = false;
                    this.isDrawing = false;
                    this.unbindMarqueeListeners();
                    this.drawnArea = null;
                    Alpine.store('feSel').clearMarquee();
                },
                uploadDroppedFiles(files) {
                    if (!this.abilities.upload) return;
                    if (!files || !files.length) return;
                    const filtered = [...files].filter(file => this.isAllowedFile(file));
                    if (!filtered.length) {
                        Alpine.store('feUpload').error(this.translations?.validation?.invalid_format || 'Invalid file format');
                        return;
                    }
                    this.onUploadStart();
                    this.$wire.uploadMultiple(
                        'files',
                        filtered,
                        () => { this.onUploadFinish(); },
                        () => { this.onUploadError(); },
                        (event) => { this.onUploadProgress(event.detail.progress); }
                    );
                },
                pickAndUploadFiles(event) {
                    const input = event.target;
                    const files = input?.files;
                    if (!files || !files.length) return;
                    this.uploadDroppedFiles(files);
                    input.value = '';
                },
                isAllowedFile(file) {
                    const accept = (document.getElementById('fileInput')?.accept || '')
                        .split(',')
                        .map(s => s.trim().toLowerCase())
                        .filter(Boolean);
                    if (!accept.length) return true;
                    const name = (file.name || '').toLowerCase();
                    const type = (file.type || '').toLowerCase();
                    const extOk = accept.some(a => a.startsWith('.') && name.endsWith(a));
                    if (extOk) return true;
                    return accept.some(a => {
                        if (a.startsWith('.')) return false;
                        if (a.endsWith('/*')) return type.startsWith(a.replace('/*', '/'));
                        return type !== '' && type === a;
                    });
                },
            };
        }

        document.addEventListener('livewire:initialized', () => {
            Livewire.on('new-folder-created', function () {
                const checkExist = setInterval(function() {
                    let input = document.getElementById('new-folder-name');
                    if (input) {
                        input.focus();
                        input.select();
                        clearInterval(checkExist);
                    }
                }, 100);
            });

            Livewire.on('focus-rename-input', function () {
                const checkExist = setInterval(function() {
                    let input = document.getElementById('rename-input');
                    if (input) {
                        input.focus();
                        input.select();
                        clearInterval(checkExist);
                    }
                }, 50);
            });
        });

        window.qfCtxFlyout = qfCtxFlyout;
        window.FileExplorerUi = FileExplorerUi;
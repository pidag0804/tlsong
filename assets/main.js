// ================================================================= //
//                      YouTube IFrame Player API 邏輯               //
// ================================================================= //

// 這個全域函式會由 YouTube 的 API 指令碼自動呼叫
function onYouTubeIframeAPIReady() {
    const players = document.querySelectorAll('[data-videoid]');
    players.forEach((playerDiv, index) => {
        const videoId = playerDiv.dataset.videoid;
        try {
            new YT.Player(`player-${index}`, {
                height: '100%',
                width: '100%',
                videoId: videoId,
                playerVars: {
                    'playsinline': 1
                },
                events: {
                    'onError': onPlayerError
                }
            });
        } catch (e) {
            console.error('Failed to create YouTube player:', e);
            const errorContainer = document.getElementById(`player-error-container-${index}`);
            if(errorContainer) {
                errorContainer.innerHTML = `<div class="player-overlay visible"><p class="player-overlay-message">播放器初始化失敗。</p></div>`;
            }
        }
    });
}

// 當播放器發生錯誤時，API 會呼叫這個函式
function onPlayerError(event) {
    const playerElement = event.target.getIframe();
    const playerId = playerElement.id;
    const playerIndex = playerId.split('-')[1];
    const errorContainer = document.getElementById(`player-error-container-${playerIndex}`);
    const youtubeEmbedDiv = errorContainer ? errorContainer.closest('.youtube-embed') : null;
    const originalUrl = youtubeEmbedDiv ? youtubeEmbedDiv.dataset.url : 'https://www.youtube.com';

    let errorMessage = '發生未知錯誤，影片無法播放。';
    
    // 根據 YouTube API 的錯誤碼提供更精準的訊息
    if (event.data === 101 || event.data === 150) {
        errorMessage = '影片擁有者已停用在外部網站播放。';
    } else if (event.data === 2) {
        errorMessage = '影片 ID 格式錯誤。';
    } else if (event.data === 5) {
        errorMessage = 'HTML5 播放器發生錯誤。';
    }

    if (errorContainer) {
        const overlayHTML = `
            <div class="player-overlay visible">
                <p class="player-overlay-message">${errorMessage}</p>
                <a href="${originalUrl}" target="_blank" class="player-overlay-button">在 YouTube 上觀看</a>
            </div>
        `;
        errorContainer.innerHTML = overlayHTML;
    }
}


// ================================================================= //
//                      主要應用程式邏輯 (表單、Modal 等)            //
// ================================================================= //

document.addEventListener('DOMContentLoaded', function() {
    
    // --- 推薦表單邏輯 (form.php) ---
    const recommendForm = document.getElementById('recommendForm');
    if (recommendForm) {
        const statusDiv = document.getElementById('general-status');
        recommendForm.addEventListener('submit', function(e) {
            e.preventDefault();
            statusDiv.style.display = 'none';
            const formData = new FormData(recommendForm);
            const apiUrl = recommendForm.getAttribute('action');
            fetch(apiUrl, { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    statusDiv.textContent = data.message;
                    statusDiv.className = 'status-message success';
                    statusDiv.style.display = 'block';
                    setTimeout(() => { window.location.href = 'index.php?page=preview'; }, 1500);
                } else {
                    statusDiv.textContent = data.message;
                    statusDiv.className = 'status-message error';
                    statusDiv.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                statusDiv.textContent = '發生網路錯誤，請稍後再試。';
                statusDiv.className = 'status-message error';
                statusDiv.style.display = 'block';
            });
        });
        
        const addSongBtn = document.getElementById('add-song-btn');
        const songEntriesContainer = document.getElementById('song-entries');
        if(addSongBtn && songEntriesContainer) {
            const MAX_SONGS = 10;
            let currentSongCount = parseInt(songEntriesContainer.dataset.existingCount || '0');
            const updateButtonState = () => {
                if (songEntriesContainer.children.length + currentSongCount >= MAX_SONGS) {
                    addSongBtn.disabled = true;
                    addSongBtn.textContent = '已達推薦上限';
                } else {
                    addSongBtn.disabled = false;
                    addSongBtn.textContent = '新增一首推薦';
                }
            };
            addSongBtn.addEventListener('click', () => {
                const newEntry = songEntriesContainer.children[0].cloneNode(true);
                newEntry.querySelectorAll('input').forEach(input => input.value = '');
                let removeBtn = newEntry.querySelector('.btn-remove');
                if (!removeBtn) {
                    removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'btn-remove';
                    newEntry.appendChild(removeBtn);
                }
                removeBtn.textContent = 'X';
                removeBtn.addEventListener('click', () => {
                    newEntry.remove();
                    updateButtonState();
                });
                songEntriesContainer.appendChild(newEntry);
                updateButtonState();
            });
            updateButtonState();
        }
    }

    // --- 編輯功能 Modal 邏輯 (preview.php) ---
    const modalBackdrop = document.getElementById('edit-modal-backdrop');
    if (modalBackdrop) {
        const editForm = document.getElementById('editForm');
        const cancelBtn = document.getElementById('cancel-edit-btn');
        const editStatusDiv = document.getElementById('edit-status');
        let currentEditingCard = null;

        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', (e) => {
                currentEditingCard = e.target.closest('.preview-item');
                document.getElementById('edit-song-id').value = currentEditingCard.dataset.id;
                document.getElementById('edit-singer').value = currentEditingCard.dataset.singer;
                document.getElementById('edit-song-title').value = currentEditingCard.dataset.songTitle;
                document.getElementById('edit-song-url').value = currentEditingCard.dataset.songUrl;
                modalBackdrop.classList.add('visible');
            });
        });

        const closeModal = () => {
            modalBackdrop.classList.remove('visible');
            editStatusDiv.style.display = 'none';
            currentEditingCard = null;
        };
        
        cancelBtn.addEventListener('click', closeModal);
        modalBackdrop.addEventListener('click', (e) => {
            if (e.target === modalBackdrop) closeModal();
        });

        editForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(editForm);
            const apiUrl = editForm.getAttribute('action') || 'api.php';

            fetch(apiUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    currentEditingCard.querySelector('.singer-text').textContent = formData.get('singer');
                    currentEditingCard.querySelector('.song-title-text').textContent = formData.get('song_title');
                    currentEditingCard.dataset.singer = formData.get('singer');
                    currentEditingCard.dataset.songTitle = formData.get('song_title');
                    currentEditingCard.dataset.songUrl = formData.get('song_url');
                    
                    editStatusDiv.className = 'status-message success';
                    editStatusDiv.textContent = data.message + ' (刷新後播放器將更新)';
                    editStatusDiv.style.display = 'block';
                    setTimeout(closeModal, 2000);
                } else {
                    editStatusDiv.className = 'status-message error';
                    editStatusDiv.textContent = data.message;
                    editStatusDiv.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Edit Error:', error);
                editStatusDiv.className = 'status-message error';
                editStatusDiv.textContent = '發生網路錯誤，請稍後再試。';
                editStatusDiv.style.display = 'block';
            });
        });
    }

    // --- 匯出頁面邏輯 (export.php) ---
    const copyBtn = document.getElementById('copy-btn');
    if (copyBtn) {
        copyBtn.addEventListener('click', () => {
            const exportBox = document.getElementById('export-content');
            const textToCopy = exportBox.innerText;

            if (navigator.clipboard) {
                navigator.clipboard.writeText(textToCopy).then(() => {
                    copyBtn.textContent = '已複製！';
                    setTimeout(() => { copyBtn.textContent = '一鍵複製內容'; }, 2000);
                });
            } else {
                alert('您的瀏覽器不支援自動複製功能，請手動選取複製。');
            }
        });
    }

    // --- 模糊搜索 Modal 邏輯 (form.php) ---
    const openSearchBtn = document.getElementById('open-search-btn');
    const searchModalBackdrop = document.getElementById('search-modal-backdrop');
    if (openSearchBtn && searchModalBackdrop) {
        const closeSearchBtn = document.getElementById('close-search-btn');
        const searchInput = document.getElementById('search-input');
        const searchResultsContainer = document.getElementById('search-results');
        let searchTimeout;

        const openSearchModal = () => searchModalBackdrop.classList.add('visible');
        const closeSearchModal = () => {
            searchModalBackdrop.classList.remove('visible');
            searchInput.value = '';
            searchResultsContainer.innerHTML = '<p class="search-placeholder">請輸入關鍵字開始查詢</p>';
        };

        openSearchBtn.addEventListener('click', openSearchModal);
        closeSearchBtn.addEventListener('click', closeSearchModal);
        searchModalBackdrop.addEventListener('click', (e) => {
            if (e.target === searchModalBackdrop) closeSearchModal();
        });

        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            const query = searchInput.value.trim();

            if (query.length < 1) {
                searchResultsContainer.innerHTML = '<p class="search-placeholder">請輸入關鍵字開始查詢</p>';
                return;
            }
            
            searchResultsContainer.innerHTML = '<p class="search-placeholder">正在查詢中...</p>';

            searchTimeout = setTimeout(() => {
                const formData = new FormData();
                formData.append('action', 'search');
                formData.append('query', query);

                fetch('api.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        if (data.results.length > 0) {
                            let html = '';
                            data.results.forEach(song => {
                                html += `<div class="search-result-item">
                                            <div>
                                                <span class="singer">${escapeHtml(song.singer)}</span> - 
                                                <span class="song-title">${escapeHtml(song.song_title)}</span>
                                            </div>
                                         </div>`;
                            });
                            searchResultsContainer.innerHTML = html;
                        } else {
                            searchResultsContainer.innerHTML = '<p class="search-placeholder">找不到符合的歌曲</p>';
                        }
                    } else {
                        searchResultsContainer.innerHTML = `<p class="search-placeholder" style="color: var(--glow-color-error);">${data.message}</p>`;
                    }
                })
                .catch(error => {
                    console.error('Search Error:', error);
                    searchResultsContainer.innerHTML = '<p class="search-placeholder" style="color: var(--glow-color-error);">查詢時發生錯誤</p>';
                });
            }, 300);
        });
    }

    // Helper function to prevent XSS
    function escapeHtml(unsafe) {
        return unsafe
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }
});
(function () {
    "use strict";

    const CONFIG = window.VELAS_CONFIG || {};
    const API_BASE = CONFIG.API_BASE || "./api";
    const MY_TOKENS_KEY = CONFIG.MY_TOKENS_KEY || "portal_oraciones_owner_tokens_v1";
    const SHARE_TOKENS_KEY = CONFIG.SHARE_TOKENS_KEY || "portal_oraciones_share_tokens_v1";

    const $ = (id) => document.getElementById(id);

    function getMyTokens() {
        try {
            return JSON.parse(localStorage.getItem(MY_TOKENS_KEY) || "{}");
        } catch (e) {
            return {};
        }
    }
    function getShareTokens() {
        try {
            return JSON.parse(localStorage.getItem(SHARE_TOKENS_KEY) || "{}");
        } catch (e) {
            return {};
        }
    }

    function showNotice(msg, type = "ok") {
        const el = $("notice");
        el.textContent = msg;
        el.className = "notice show " + (type === "ok" ? "ok" : "err");

        // ✅ asegura que se vea
        el.scrollIntoView({ behavior: "smooth", block: "center" });

        setTimeout(() => el.classList.remove("show"), 4500);
    }

    function openModal(html, canShare = false, shareUrl = "") {
        $("modalBody").innerHTML = html;
        $("copyNotice").className = "notice";
        $("copyNotice").textContent = "";
        $("shareRow").style.display = canShare ? "flex" : "none";
        if (canShare) {
            $("shareLink").value = shareUrl;

            var whatsappText = "Encendí una vela en Fundación Las Rosas 🕯️\n\n";
            whatsappText += "Puedes verla y acompañar esta intención aquí:\n";
            whatsappText += shareUrl;

            $("whatsappShare").href = "https://wa.me/?text=" + encodeURIComponent(whatsappText);
        }
        $("modalBack").classList.add("show");
    }

    function closeModal() {
        $("modalBack").classList.remove("show");

        selectedId = null;
        autoOpenDone = true;

        const url = new URL(window.location.href);
        url.searchParams.delete("candle");
        url.searchParams.delete("share_token");
        url.searchParams.delete("owner_token");
        url.searchParams.delete("mail");

        history.replaceState({}, "", url.toString());
    }


    $("closeModal").onclick = closeModal;
    $("modalBack").onclick = (e) => { if (e.target === $("modalBack")) closeModal(); };

    $("copyLink").onclick = async () => {
        try {
            await navigator.clipboard.writeText($("shareLink").value);
            $("copyNotice").textContent = "✅ Link copiado";
            $("copyNotice").className = "notice show ok";
        } catch (e) {
            $("copyNotice").textContent = "⚠️ Copia manualmente el link";
            $("copyNotice").className = "notice show err";
        }
    };

    function makeShareUrl(id) {
        const url = new URL(window.location.href);
        url.searchParams.set("candle", id);

        const shareTokens = getShareTokens();
        const shareToken = shareTokens[id];

        if (shareToken) {
            url.searchParams.set("share_token", shareToken);
        }

        url.searchParams.delete("owner_token");
        url.searchParams.delete("mail");

        return url.toString();
    }

    async function apiGet(path) {
        const r = await fetch(API_BASE + "/" + path);
        const text = await r.text();

        try {
            return JSON.parse(text);
        } catch (e) {
            return {
                ok: false,
                error: "Respuesta inválida del servidor",
                raw: text
            };
        }
    }

    async function apiPost(path, data) {
        const r = await fetch(API_BASE + "/" + path, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data)
        });

        const text = await r.text();

        try {
            return JSON.parse(text);
        } catch (e) {
            return {
                ok: false,
                error: "Respuesta inválida del servidor",
                raw: text
            };
        }
    }

    const parseDate = (iso) => new Date(iso);
    const fmtTime = (iso) => parseDate(iso).toLocaleTimeString(undefined, { hour: "2-digit", minute: "2-digit" });
    const fmtDT = (iso) => parseDate(iso).toLocaleString();
    const escapeHtml = (s) => String(s ?? "")
        .replaceAll("&", "&amp;").replaceAll("<", "&lt;").replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;").replaceAll("'", "&#039;");

    let selectedId = null;
    let autoOpenDone = false;
    let isRefreshing = false;

    let allCandles = [];
    let currentPage = 1;
    const candlesPerPage = 10;

    function renderPagination(totalItems) {
        const pagination = $("pagination");
        if (!pagination) return;

        const totalPages = Math.ceil(totalItems / candlesPerPage);

        if (totalPages <= 1) {
            pagination.innerHTML = "";
            return;
        }

        const isMobile = window.innerWidth <= 520;
        const maxVisiblePages = isMobile ? 3 : 5;

        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = startPage + maxVisiblePages - 1;

        if (endPage > totalPages) {
            endPage = totalPages;
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }

        let html = "";

        html += `
        <button type="button" ${currentPage === 1 ? "disabled" : ""} data-page="${currentPage - 1}" aria-label="Página anterior">
          ←
        </button>
      `;

        if (startPage > 1 && !isMobile) {
            html += `<button type="button" data-page="1">1</button>`;
            if (startPage > 2) {
                html += `<button type="button" disabled>…</button>`;
            }
        }

        for (let page = startPage; page <= endPage; page++) {
            html += `
          <button type="button" class="${page === currentPage ? "active" : ""}" data-page="${page}" aria-label="Página ${page}">
            ${page}
          </button>
        `;
        }

        if (endPage < totalPages && !isMobile) {
            if (endPage < totalPages - 1) {
                html += `<button type="button" disabled>…</button>`;
            }
            html += `<button type="button" data-page="${totalPages}">${totalPages}</button>`;
        }

        html += `
        <button type="button" ${currentPage === totalPages ? "disabled" : ""} data-page="${currentPage + 1}" aria-label="Página siguiente">
          →
        </button>
      `;

        pagination.innerHTML = html;

        pagination.querySelectorAll("button[data-page]").forEach(btn => {
            btn.addEventListener("click", () => {
                const nextPage = Number(btn.dataset.page);
                if (!nextPage || nextPage === currentPage) return;
                currentPage = nextPage;
                renderCandles();
                window.scrollTo({ top: pagination.offsetTop - 120, behavior: "smooth" });
            });
        });
    }



    function renderCandles() {
        const box = $("candles");
        if (!box) return;

        if (!allCandles.length) {
            box.innerHTML = `
                <div class="empty-state">
                    <h2>No hay velas encendidas por el momento</h2>
                    <p>Te invitamos a encender una vela y compartir una intención.</p>
                </div>
            `;

            renderPagination(0);
            return;
        }

        const myTokens = getMyTokens();

        const totalPages = Math.ceil(allCandles.length / candlesPerPage);
        if (currentPage > totalPages && totalPages > 0) {
            currentPage = totalPages;
        }
        if (currentPage < 1) {
            currentPage = 1;
        }

        const start = (currentPage - 1) * candlesPerPage;
        const end = start + candlesPerPage;
        const candlesToShow = allCandles.slice(start, end);

        const fragment = document.createDocumentFragment();

        for (const c of candlesToShow) {
            const isMine = !!myTokens[c.id];
            const tile = document.createElement("div");
            tile.className = "cTile" + (isMine ? " mine" : "") + (selectedId === c.id ? " selected" : "");

            const remainingMs = Math.max(0, parseDate(c.expiresAt) - new Date());
            const remainingH = Math.ceil(remainingMs / (60 * 60 * 1000));

            tile.innerHTML = `
          ${isMine ? `<span class="badgeMine">Mi vela</span>` : ``}
          <img class="candleImg" src="./assets/img/candle.PNG" alt="Vela encendida">
          <div style="font-size:15px; margin-bottom:6px;">
            <strong>${c.initials}</strong>
            <span class="muted">· ${c.publicDate}</span>
          </div>

          <div class="meta">
            <span>⏳ ${remainingH}h</span>
            <span class="mono">${c.id.slice(-6)}</span>
          </div>
          <div class="highlight"></div>
        `;

            tile.onclick = () => onCandleClick(c, isMine);
            fragment.appendChild(tile);
        }

        box.innerHTML = "";
        box.appendChild(fragment);

        renderPagination(allCandles.length);
    }

    async function refresh() {
        if (isRefreshing) return;
        isRefreshing = true;

        try {
            $("clock").textContent = new Date().toLocaleTimeString(undefined, {
                hour: "2-digit",
                minute: "2-digit",
                second: "2-digit"
            });

            const [countRes, listRes] = await Promise.all([
                apiGet("count_candles.php"),
                apiGet("list_candles.php")
            ]);

            // Si falla list_candles, no se puede pintar la vista
            if (!listRes.ok) {
                console.warn("Error en list_candles", listRes);
                showNotice("No pudimos cargar las velas en este momento. Intenta nuevamente.", "err");
                return;
            }

            // count puede fallar, pero no bloquea la vista
            if (countRes.ok) {
                $("countActive").textContent = countRes.count ?? 0;
            } else {
                console.warn("count_candles falló:", countRes.error);
            }

            const candles = Array.isArray(listRes.candles) ? listRes.candles : [];

            const totalEl = document.getElementById("totalVelas");
            const activeHeaderEl = document.getElementById("velasActivas");

            if (totalEl) {
                if (countRes && typeof countRes.total !== "undefined") {
                    totalEl.textContent = countRes.total;
                } else {
                    totalEl.textContent = candles.length;
                }
            }

            if (activeHeaderEl) {
                if (countRes && typeof countRes.count !== "undefined") {
                    activeHeaderEl.textContent = countRes.count;
                } else {
                    activeHeaderEl.textContent = candles.length;
                }
            }

            const next = candles
                .map(c => c.expiresAt)
                .sort((a, b) => new Date(a) - new Date(b))[0];

            $("nextExpire").textContent = next ? fmtTime(next) : "—";

            const url = new URL(window.location.href);
            selectedId = url.searchParams.get("candle") || null;


            allCandles = candles;
            $("countActive").textContent = allCandles.length;

            const totalPages = Math.ceil(allCandles.length / candlesPerPage);
            if (currentPage > totalPages && totalPages > 0) {
                currentPage = totalPages;
            }

            renderCandles();

            if (selectedId && !autoOpenDone) {
                const selected = allCandles.find(function (c) {
                    return c.id === selectedId;
                });

                if (selected) {
                    autoOpenDone = true;
                    const myTokens = getMyTokens();
                    const isMine = !!myTokens[selected.id];
                    await onCandleClick(selected, isMine, false);
                } else {
                    autoOpenDone = true;
                    openModal(
                        '<div><strong>No se encontraron velas asociadas a este enlace.</strong></div>' +
                        '<div style="margin-top:10px;">El enlace puede haber expirado, estar incompleto o haber sido modificado.</div>',
                        false,
                        ''
                    );
                }
            }

        } catch (error) {
            console.error("Error en refresh()", error);
        } finally {
            isRefreshing = false;
        }
    }

    async function onCandleClick(c, isMine, updateUrl = true) {
        if (updateUrl) {
            const url = new URL(window.location.href);
            url.searchParams.set("candle", c.id);
            url.searchParams.delete("share_token");
            url.searchParams.delete("owner_token");
            url.searchParams.delete("mail");

            history.replaceState({}, "", url.toString());
            selectedId = c.id;
            autoOpenDone = true;
        }

        const publicInfo = `
        <div><strong>Iniciales:</strong> ${escapeHtml(c.initials)}</div>
        <div><strong>Fecha:</strong> ${escapeHtml(c.publicDate)}</div>
      `;

        const ownerBaseInfo = `
        <div><strong>Iniciales:</strong> ${escapeHtml(c.initials)}</div>
        <div><strong>Fecha:</strong> ${escapeHtml(c.publicDate)}</div>
        <div><strong>Encendida:</strong> ${escapeHtml(fmtDT(c.createdAt))}</div>
        <div><strong>Expira:</strong> ${escapeHtml(fmtDT(c.expiresAt))}</div>
      `;

        const url = new URL(window.location.href);
        const urlCandleId = url.searchParams.get("candle") || "";
        const urlShareToken = url.searchParams.get("share_token") || "";

        const shareTokens = getShareTokens();
        const storedShareToken = shareTokens[c.id] || "";

        const share_token = (urlCandleId === c.id && urlShareToken !== "")
            ? urlShareToken
            : storedShareToken;

        if (!isMine && share_token === "") {
            openModal(publicInfo, false, "");
            return;
        }

        const myTokens = getMyTokens();
        const owner_token = myTokens[c.id];

        const res = await apiPost("my_candle.php", {
            id: c.id,
            owner_token,
            share_token
        });

        if (!res.ok) {
            openModal(
                ownerBaseInfo + `<hr style="border:none;border-top:1px solid rgba(34,48,82,.6);margin:14px 0;"><div>⚠️ No autorizado o expirado.</div>`,
                false,
                ""
            );
            return;
        }

        if (res.type === "owner") {
            const p = res.candle.private;

            const html = `
                ${ownerBaseInfo}
                <hr style="border:none;border-top:1px solid rgba(34,48,82,.6);margin:14px 0;">
                <div><strong>Nombre:</strong> ${escapeHtml(p.name)}</div>
                <div><strong>Email:</strong> ${escapeHtml(p.email)}</div>
                <div style="margin-top:10px;"><strong>Petición:</strong><br>${escapeHtml(p.request).replaceAll("\n", "<br>")}</div>
            `;

            openModal(html, true, makeShareUrl(c.id));
            return;
        }

        if (res.type === "shared") {
            const s = res.candle.shared;

            const html = `
                ${ownerBaseInfo}
                <hr style="border:none;border-top:1px solid rgba(34,48,82,.6);margin:14px 0;">
                <div style="margin-top:10px;"><strong>Petición:</strong><br>${escapeHtml(s.request).replaceAll("\n", "<br>")}</div>
            `;

            openModal(html, false, "");
            return;
        }

        openModal(publicInfo, false, "");
    }

    // Leer token en velas.html
    function bootstrapOwnerTokenFromQuery() {
        const url = new URL(window.location.href);
        const candleId = url.searchParams.get("candle");
        const ownerToken = url.searchParams.get("owner_token");

        if (candleId && ownerToken) {
            const key = "portal_oraciones_owner_tokens_v1";

            let tokens = {};
            try {
                tokens = JSON.parse(localStorage.getItem(key) || "{}");
            } catch (e) {
                tokens = {};
            }

            tokens[candleId] = ownerToken;
            localStorage.setItem(key, JSON.stringify(tokens));

            // limpiar token de la URL apenas se guarda
            url.searchParams.delete("owner_token");
            history.replaceState({}, "", url.toString());
        }
    }

    function bootstrapShareTokenFromQuery() {
        const url = new URL(window.location.href);
        const candleId = url.searchParams.get("candle");
        const shareToken = url.searchParams.get("share_token");

        if (candleId && shareToken) {
            const tokens = getShareTokens();
            tokens[candleId] = shareToken;
            localStorage.setItem(SHARE_TOKENS_KEY, JSON.stringify(tokens));
        }
    }

    // ✅ Mostrar mensaje de correo si viene desde enciende.html
    (function showMailNoticeFromQuery() {
        const url = new URL(window.location.href);
        const mail = url.searchParams.get("mail");

        if (!mail) return;

        if (mail === "ok") {
            showNotice("📩 Se ha enviado un correo de confirmación al solicitante.", "ok");
        } else if (mail === "fail") {
            showNotice("⚠️ La vela fue encendida, pero no se pudo enviar el correo al solicitante.", "err");
        }

        // ✅ opcional: limpiar el parámetro para que no se repita al recargar
        url.searchParams.delete("mail");
        history.replaceState({}, "", url.toString());
    })();

    window.addEventListener("resize", () => {
        renderPagination(allCandles.length);
    });

    setInterval(refresh, 15000);
    bootstrapOwnerTokenFromQuery();
    bootstrapShareTokenFromQuery();
    refresh();
})();
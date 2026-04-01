import './bootstrap';
import Alpine from 'alpinejs';
import { marked } from 'marked';
import DOMPurify from 'dompurify';

marked.setOptions({ gfm: true, breaks: true });

window.renderMarkdown = function renderMarkdown(md) {
    if (md == null || String(md).trim() === '') {
        return '';
    }
    const raw = marked.parse(String(md));
    return DOMPurify.sanitize(raw, { USE_PROFILES: { html: true } });
};

window.Alpine = Alpine;

Alpine.start();
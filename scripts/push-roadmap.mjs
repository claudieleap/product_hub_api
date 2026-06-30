#!/usr/bin/env node
/**
 * Sincroniza roadmap-state.json com a API (sync upsert).
 * Uso: node scripts/push-roadmap.mjs [arquivo.json] [API_BASE_URL]
 */
import fs from 'node:fs';

const file = process.argv[2] || 'database/roadmap-state.json';
const baseUrl = (process.env.VITE_API_BASE_URL || process.argv[3] || 'http://localhost:8000/api/v1').replace(/\/$/, '');
const apiKey = process.env.VITE_API_KEY || process.argv[4] || '';

if (!fs.existsSync(file)) {
    console.error(`Arquivo não encontrado: ${file}`);
    process.exit(1);
}

const payload = JSON.parse(fs.readFileSync(file, 'utf8'));

const headers = {
    Accept: 'application/json',
    'Content-Type': 'application/json'
};

if (apiKey) {
    headers['X-API-Key'] = apiKey;
}

const response = await fetch(`${baseUrl}/roadmap/sync`, {
    method: 'POST',
    headers,
    body: JSON.stringify(payload)
});

let body = null;

try {
    body = await response.json();
} catch {
    body = null;
}

if (!response.ok) {
    console.error(body?.message || `Erro HTTP ${response.status}`);
    process.exit(1);
}

const data = body?.data ?? body;
console.log(
    `Roadmap sincronizado: ${data?.items?.length ?? payload.items?.length ?? 0} itens, ` +
        `${data?.customProducts?.length ?? payload.customProducts?.length ?? 0} módulos.`
);

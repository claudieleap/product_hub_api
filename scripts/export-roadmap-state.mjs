#!/usr/bin/env node
/**
 * Gera database/roadmap-state.json a partir do seed do frontend.
 * Uso: node scripts/export-roadmap-state.mjs
 */
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const seedModuleUrl = pathToFileURL(
    path.resolve(__dirname, '../../product_hub_app/src/data/roadmapSeedData.js')
).href;

const { IMPLEMENTED_SEED_IDS, ROADMAP_SEED_ITEMS } = await import(seedModuleUrl);

const implemented = new Set(IMPLEMENTED_SEED_IDS);
const items = ROADMAP_SEED_ITEMS.filter((item) => !implemented.has(item.id));

const payload = {
    items,
    customProducts: [],
    deletedSeedIds: [...IMPLEMENTED_SEED_IDS]
};

const out = path.resolve(__dirname, '../database/roadmap-state.json');
fs.writeFileSync(out, JSON.stringify(payload, null, 2));

console.log(`Exportado: ${items.length} itens, ${payload.deletedSeedIds.length} seeds implementados marcados.`);
console.log(`Arquivo: ${out}`);

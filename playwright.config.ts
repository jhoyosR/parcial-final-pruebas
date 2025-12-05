import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: 'tests/playwright',
  use: {
    baseURL: 'http://localhost',
    headless: true,
  },
});
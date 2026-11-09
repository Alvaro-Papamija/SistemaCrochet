import { test, expect } from '@playwright/test';

test('storefront view and adding product to cart', async ({ page }) => {
  await page.goto('http://localhost:3000');

  await expect(page).toHaveTitle(/Maraña Crochet/);

  const welcomeModal = page.locator('.fixed.inset-0.z-50');
  await expect(welcomeModal).toBeVisible();

  const continueButton = page.getByRole('button', { name: 'Continuar como Invitado y Explorar Tienda \u2192' });
  await continueButton.click();

  await expect(welcomeModal).not.toBeVisible();

  // Lulú is $24,990 COP
  const productCard = page.locator('.group').filter({ hasText: 'Lulú la Arañita Cósmica' }).first();
  await expect(productCard).toBeVisible();

  const addToCartButton = productCard.getByRole('button', { name: 'Adoptar' });
  await addToCartButton.click();

  const cartButton = page.getByRole('button', { name: 'Carrito de compras' });
  await cartButton.click();

  const cartItemTitle = page.locator('h4.font-label-lg').filter({ hasText: 'Lulú la Arañita Cósmica' }).first();
  await expect(cartItemTitle).toBeVisible();
});

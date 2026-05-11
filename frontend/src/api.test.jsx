import { afterEach, describe, expect, it, vi } from 'vitest';
import { apiRequest } from './api.js';

describe('apiRequest', () => {
  afterEach(() => {
    vi.restoreAllMocks();
  });

  it('adds JSON and bearer headers', async () => {
    const fetchMock = vi.spyOn(globalThis, 'fetch').mockResolvedValue({
      ok: true,
      json: async () => ({ ok: true }),
    });

    const payload = await apiRequest('/api/me', {
      token: 'token-value',
      method: 'POST',
      body: { hello: 'world' },
    });

    expect(payload).toEqual({ ok: true });
    expect(fetchMock).toHaveBeenCalledWith('http://localhost:8000/api/me', {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        Authorization: 'Bearer token-value',
      },
      body: JSON.stringify({ hello: 'world' }),
    });
  });

  it('throws the API error message when the request fails', async () => {
    vi.spyOn(globalThis, 'fetch').mockResolvedValue({
      ok: false,
      json: async () => ({ error: 'Invalid credentials.' }),
    });

    await expect(apiRequest('/api/auth/login')).rejects.toThrow('Invalid credentials.');
  });
});

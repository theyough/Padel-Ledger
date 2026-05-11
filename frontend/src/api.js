const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000';

export async function apiRequest(path, { token, method = 'GET', body } = {}) {
  const response = await fetch(`${API_URL}${path}`, {
    method,
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
    body: body ? JSON.stringify(body) : undefined,
  });

  const payload = await response.json().catch(() => ({}));

  if (!response.ok) {
    throw new Error(payload.error || 'Unexpected error.');
  }

  return payload;
}

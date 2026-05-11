import { render, screen } from '@testing-library/react';
import { beforeEach, describe, expect, it } from 'vitest';
import App from './App.jsx';

describe('App', () => {
  beforeEach(() => {
    window.localStorage.clear();
    window.history.replaceState(null, '', '/');
  });

  it('renders anonymous shell with auth layout', () => {
    render(<App />);

    expect(document.querySelector('.auth-layout')).toBeTruthy();
    expect(screen.getByText('Niveaux club')).toBeInTheDocument();
  });
});

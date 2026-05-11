import { render, screen } from '@testing-library/react';
import { beforeEach, describe, expect, it } from 'vitest';
import App from './App.jsx';

describe('App', () => {
  beforeEach(() => {
    window.localStorage.clear();
    window.history.replaceState(null, '', '/');
  });

  it('renders the login screen for anonymous players', () => {
    render(<App />);

    expect(screen.getAllByRole('button', { name: /sign in/i })).toHaveLength(2);
    expect(screen.getByText('Padel Levels')).toBeInTheDocument();
    expect(screen.getByText('Club levels')).toBeInTheDocument();
  });
});

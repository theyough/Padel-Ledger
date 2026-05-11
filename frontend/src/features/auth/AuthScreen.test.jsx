import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';
import { AuthScreen } from './AuthScreen.jsx';

describe('AuthScreen', () => {
  it('renders login mode with club level reference', () => {
    render(<AuthScreen onSession={() => {}} />);

    expect(screen.getByRole('button', { name: 'Connexion' })).toBeInTheDocument();
    expect(screen.getByRole('button', { name: 'Se connecter' })).toBeInTheDocument();
    expect(screen.getByText('Niveaux padel')).toBeInTheDocument();
    expect(screen.getByText('Niveaux club')).toBeInTheDocument();
  });
});

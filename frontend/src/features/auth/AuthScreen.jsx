import { useState } from 'react';
import { ShieldCheck, Trophy } from 'lucide-react';
import { apiRequest } from '../../api.js';
import { Notice } from '../../components/Notice.jsx';
import { LEVEL_ROWS } from './levelReference.js';
import { RegisterForm } from './RegisterForm.jsx';

export function AuthScreen({ onSession }) {
  const [mode, setMode] = useState('login');
  const [error, setError] = useState('');

  async function submitLogin(event) {
    event.preventDefault();
    setError('');
    const form = new FormData(event.currentTarget);
    try {
      const payload = await apiRequest('/api/auth/login', {
        method: 'POST',
        body: {
          email: form.get('email'),
          password: form.get('password'),
        },
      });
      onSession(payload.token, payload.player);
    } catch (exception) {
      setError(exception.message);
    }
  }

  return (
    <div className="auth-layout">
      <section className="auth-panel">
        <div className="auth-header">
          <div className="brand compact">
            <Trophy size={24} />
            <strong>Niveaux padel</strong>
          </div>
          <div className="segmented">
            <button type="button" className={mode === 'login' ? 'active' : ''} onClick={() => setMode('login')}>Connexion</button>
            <button type="button" className={mode === 'register' ? 'active' : ''} onClick={() => setMode('register')}>Compte</button>
          </div>
        </div>

        {error && <Notice type="error" text={error} />}

        {mode === 'login' ? (
          <form className="form-stack" onSubmit={submitLogin}>
            <label>
              E-mail
              <input name="email" type="email" autoComplete="email" required />
            </label>
            <label>
              Mot de passe
              <input name="password" type="password" autoComplete="current-password" minLength={8} required />
            </label>
            <button className="primary-button" type="submit">
              <ShieldCheck size={18} />
              Se connecter
            </button>
          </form>
        ) : (
          <RegisterForm onSession={onSession} onError={setError} />
        )}
      </section>

      <section className="level-reference">
        <h2>Niveaux club</h2>
        <div className="level-table">
          {LEVEL_ROWS.map(([level, name, description, tournaments]) => (
            <div className="level-row" key={level}>
              <span>N{level}</span>
              <strong>{name}</strong>
              <p>{description}</p>
              <small>{tournaments}</small>
            </div>
          ))}
        </div>
      </section>
    </div>
  );
}

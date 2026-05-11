import { useEffect, useState } from 'react';
import { Plus } from 'lucide-react';
import { apiRequest } from '../../api.js';
import { INITIAL_QUESTIONNAIRE } from './questionnaireDefaults.js';
import { RangeField } from './RangeField.jsx';

export function RegisterForm({ onSession, onError }) {
  const [questionnaire, setQuestionnaire] = useState(INITIAL_QUESTIONNAIRE);
  const [estimatedLevel, setEstimatedLevel] = useState(1);

  useEffect(() => {
    const timeout = setTimeout(() => {
      apiRequest('/api/questionnaire/level', {
        method: 'POST',
        body: questionnaire,
      })
        .then(({ level }) => setEstimatedLevel(level))
        .catch(() => {});
    }, 250);

    return () => clearTimeout(timeout);
  }, [questionnaire]);

  function updateQuestionnaire(field, value) {
    setQuestionnaire((current) => ({ ...current, [field]: value }));
  }

  async function submitRegister(event) {
    event.preventDefault();
    onError('');
    const form = new FormData(event.currentTarget);
    try {
      const payload = await apiRequest('/api/auth/register', {
        method: 'POST',
        body: {
          firstName: form.get('firstName'),
          lastName: form.get('lastName'),
          email: form.get('email'),
          password: form.get('password'),
          questionnaire,
        },
      });
      onSession(payload.token, payload.player);
    } catch (exception) {
      onError(exception.message);
    }
  }

  return (
    <form className="form-stack" onSubmit={submitRegister}>
      <div className="two-columns">
        <label>
          Prénom
          <input name="firstName" required />
        </label>
        <label>
          Nom
          <input name="lastName" required />
        </label>
      </div>
      <label>
        E-mail
        <input name="email" type="email" autoComplete="email" required />
      </label>
      <label>
        Mot de passe
        <input name="password" type="password" autoComplete="new-password" minLength={8} required />
      </label>

      <div className="questionnaire">
        <div className="score-preview">
          <span>Niveau estimé</span>
          <strong>N{estimatedLevel}</strong>
        </div>
        <div className="two-columns">
          <label>
            Années de pratique
            <input
              type="number"
              min="0"
              step="0.5"
              value={questionnaire.experienceYears}
              onChange={(event) => updateQuestionnaire('experienceYears', Number(event.target.value))}
            />
          </label>
          <label>
            Matchs par mois
            <input
              type="number"
              min="0"
              value={questionnaire.matchesPerMonth}
              onChange={(event) => updateQuestionnaire('matchesPerMonth', Number(event.target.value))}
            />
          </label>
        </div>
        <label>
          Niveau tournoi le plus proche du vôtre
          <select
            value={questionnaire.competitionLevel}
            onChange={(event) => updateQuestionnaire('competitionLevel', event.target.value)}
          >
            <option value="none">Aucun</option>
            <option value="p25">P25</option>
            <option value="p50">P50</option>
            <option value="p100">P100</option>
            <option value="p250">P250</option>
            <option value="p500">P500</option>
            <option value="p1000_plus">P1000 et plus</option>
          </select>
        </label>
        <RangeField label="Régularité" value={questionnaire.consistency} onChange={(value) => updateQuestionnaire('consistency', value)} />
        <RangeField label="Utilisation des vitres" value={questionnaire.glassUsage} onChange={(value) => updateQuestionnaire('glassUsage', value)} />
        <RangeField label="Lecture du jeu" value={questionnaire.tacticalAwareness} onChange={(value) => updateQuestionnaire('tacticalAwareness', value)} />
        <RangeField label="Coups techniques" value={questionnaire.technicalShots} onChange={(value) => updateQuestionnaire('technicalShots', value)} />
      </div>

      <button className="primary-button" type="submit">
        <Plus size={18} />
        Créer mon compte
      </button>
    </form>
  );
}

import { useEffect, useState } from 'react';
import {
  Check,
  CircleAlert,
  Clock,
  LogOut,
  Mail,
  Plus,
  RefreshCw,
  ShieldCheck,
  Trophy,
  Users,
  X,
} from 'lucide-react';
import { apiRequest } from './api.js';

const levelRows = [
  ['1', 'Beginner', 'Discovering padel, the rules, and basic shots.', 'None'],
  ['2', 'Improving', 'Building rallies and reducing unforced errors.', 'None'],
  ['3', 'Elementary', 'Structured play, more accurate shots, and first glass usage.', 'Recreational - P25'],
  ['4', 'Intermediate', 'Tactical construction and better transition control.', 'P25 - P50'],
  ['5', 'Confirmed', 'Good tempo control and solid technical shots.', 'P100 - P250'],
  ['6', 'Advanced', 'Fast, tactical play with spin usage.', 'P250 - P500'],
  ['7', 'Expert', 'Strong command of all major padel patterns and techniques.', 'P500 - P1000'],
  ['8', 'Elite', 'High-level performance and national or international ranking.', 'P1000 - P1500 - P2000'],
];

const statusLabels = {
  scheduled: 'Scheduled',
  pending_score: 'Score needed',
  pending_validation: 'Pending validation',
  validated: 'Validated',
};

const initialQuestionnaire = {
  experienceYears: 0,
  matchesPerMonth: 2,
  competitionLevel: 'none',
  consistency: 2,
  glassUsage: 1,
  tacticalAwareness: 1,
  technicalShots: 1,
};

function asCollection(payload) {
  return payload.member || payload['hydra:member'] || payload;
}

function asPlayer(payload) {
  return payload.player || payload;
}

function asMatch(payload) {
  return payload.match || payload;
}

function readInitialMatchId() {
  const match = window.location.pathname.match(/^\/matches\/(\d+)/);
  return match ? Number(match[1]) : null;
}

export default function App() {
  const [token, setToken] = useState(() => localStorage.getItem('padel_token'));
  const [player, setPlayer] = useState(() => {
    const stored = localStorage.getItem('padel_player');
    return stored ? JSON.parse(stored) : null;
  });
  const [players, setPlayers] = useState([]);
  const [matches, setMatches] = useState([]);
  const [selectedMatchId, setSelectedMatchId] = useState(readInitialMatchId);
  const [selectedMatch, setSelectedMatch] = useState(null);
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const authenticated = Boolean(token && player);

  useEffect(() => {
    if (!token) return;

    apiRequest('/api/me', { token })
      .then((payload) => {
        const freshPlayer = asPlayer(payload);
        setPlayer(freshPlayer);
        localStorage.setItem('padel_player', JSON.stringify(freshPlayer));
      })
      .catch(() => logout());
  }, [token]);

  useEffect(() => {
    if (authenticated) {
      refreshData();
    }
  }, [authenticated]);

  useEffect(() => {
    if (selectedMatchId && authenticated) {
      loadMatch(selectedMatchId);
      window.history.replaceState(null, '', `/matches/${selectedMatchId}`);
    } else if (authenticated) {
      setSelectedMatch(null);
      window.history.replaceState(null, '', '/');
    }
  }, [selectedMatchId, authenticated]);

  async function refreshData() {
    setLoading(true);
    setError('');
    try {
      const [playersPayload, matchesPayload] = await Promise.all([
        apiRequest('/api/players', { token }),
        apiRequest('/api/matches', { token }),
      ]);
      setPlayers(asCollection(playersPayload));
      setMatches(asCollection(matchesPayload));
      if (selectedMatchId) {
        await loadMatch(selectedMatchId);
      }
    } catch (exception) {
      setError(exception.message);
    } finally {
      setLoading(false);
    }
  }

  async function loadMatch(matchId) {
    const payload = await apiRequest(`/api/matches/${matchId}`, { token });
    setSelectedMatch(asMatch(payload));
  }

  function storeSession(nextToken, nextPlayer) {
    setToken(nextToken);
    setPlayer(nextPlayer);
    localStorage.setItem('padel_token', nextToken);
    localStorage.setItem('padel_player', JSON.stringify(nextPlayer));
  }

  function logout() {
    setToken(null);
    setPlayer(null);
    setPlayers([]);
    setMatches([]);
    setSelectedMatchId(null);
    setSelectedMatch(null);
    localStorage.removeItem('padel_token');
    localStorage.removeItem('padel_player');
  }

  async function runAction(action, successMessage) {
    setError('');
    setMessage('');
    try {
      const result = await action();
      if (successMessage) setMessage(successMessage);
      await refreshData();
      return result;
    } catch (exception) {
      setError(exception.message);
      return null;
    }
  }

  if (!authenticated) {
    return <AuthScreen onSession={storeSession} />;
  }

  return (
    <div className="app-shell">
      <aside className="sidebar">
        <div className="brand">
          <Trophy size={24} />
          <div>
            <strong>Padel Levels</strong>
            <span>Club manager</span>
          </div>
        </div>

        <div className="profile-card">
          <div className="level-badge">N{player.level}</div>
          <div>
            <strong>{player.fullName}</strong>
            <span>{Math.round(player.rating)} pts · {player.matchCount} matches</span>
          </div>
        </div>

        <button className="nav-button" onClick={() => setSelectedMatchId(null)}>
          <Users size={18} />
          Dashboard
        </button>
        <button className="nav-button" onClick={refreshData}>
          <RefreshCw size={18} />
          Actualiser
        </button>
        <button className="nav-button danger" onClick={logout}>
          <LogOut size={18} />
          Sign out
        </button>
      </aside>

      <main className="content">
        <header className="topbar">
          <div>
            <p className="eyebrow">Level management</p>
            <h1>{selectedMatch ? `Match #${selectedMatch.id}` : 'Dashboard'}</h1>
          </div>
          <StatusPill status={selectedMatch?.status} loading={loading} />
        </header>

        {error && <Notice type="error" text={error} />}
        {message && <Notice type="success" text={message} />}

        {selectedMatch ? (
          <MatchDetail
            match={selectedMatch}
            token={token}
            runAction={runAction}
            onBack={() => setSelectedMatchId(null)}
          />
        ) : (
          <Dashboard
            players={players}
            matches={matches}
            currentPlayer={player}
            token={token}
            runAction={runAction}
            onSelectMatch={setSelectedMatchId}
          />
        )}
      </main>
    </div>
  );
}

function AuthScreen({ onSession }) {
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
            <strong>Padel Levels</strong>
          </div>
          <div className="segmented">
            <button className={mode === 'login' ? 'active' : ''} onClick={() => setMode('login')}>Sign in</button>
            <button className={mode === 'register' ? 'active' : ''} onClick={() => setMode('register')}>Account</button>
          </div>
        </div>

        {error && <Notice type="error" text={error} />}

        {mode === 'login' ? (
          <form className="form-stack" onSubmit={submitLogin}>
            <label>
              Email
              <input name="email" type="email" autoComplete="email" required />
            </label>
            <label>
              Password
              <input name="password" type="password" autoComplete="current-password" minLength={8} required />
            </label>
            <button className="primary-button" type="submit">
              <ShieldCheck size={18} />
              Sign in
            </button>
          </form>
        ) : (
          <RegisterForm onSession={onSession} onError={setError} />
        )}
      </section>

      <section className="level-reference">
        <h2>Club levels</h2>
        <div className="level-table">
          {levelRows.map(([level, name, description, tournaments]) => (
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

function RegisterForm({ onSession, onError }) {
  const [questionnaire, setQuestionnaire] = useState(initialQuestionnaire);
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
          First name
          <input name="firstName" required />
        </label>
        <label>
          Last name
          <input name="lastName" required />
        </label>
      </div>
      <label>
        Email
        <input name="email" type="email" autoComplete="email" required />
      </label>
      <label>
        Password
        <input name="password" type="password" autoComplete="new-password" minLength={8} required />
      </label>

      <div className="questionnaire">
        <div className="score-preview">
          <span>Estimated level</span>
          <strong>N{estimatedLevel}</strong>
        </div>
        <div className="two-columns">
          <label>
            Years of practice
            <input
              type="number"
              min="0"
              step="0.5"
              value={questionnaire.experienceYears}
              onChange={(event) => updateQuestionnaire('experienceYears', Number(event.target.value))}
            />
          </label>
          <label>
            Matches per month
            <input
              type="number"
              min="0"
              value={questionnaire.matchesPerMonth}
              onChange={(event) => updateQuestionnaire('matchesPerMonth', Number(event.target.value))}
            />
          </label>
        </div>
        <label>
          Tournament level closest to yours
          <select
            value={questionnaire.competitionLevel}
            onChange={(event) => updateQuestionnaire('competitionLevel', event.target.value)}
          >
            <option value="none">None</option>
            <option value="p25">P25</option>
            <option value="p50">P50</option>
            <option value="p100">P100</option>
            <option value="p250">P250</option>
            <option value="p500">P500</option>
            <option value="p1000_plus">P1000 et plus</option>
          </select>
        </label>
        <RangeField label="Consistency" value={questionnaire.consistency} onChange={(value) => updateQuestionnaire('consistency', value)} />
        <RangeField label="Glass usage" value={questionnaire.glassUsage} onChange={(value) => updateQuestionnaire('glassUsage', value)} />
        <RangeField label="Tactical reading" value={questionnaire.tacticalAwareness} onChange={(value) => updateQuestionnaire('tacticalAwareness', value)} />
        <RangeField label="Technical shots" value={questionnaire.technicalShots} onChange={(value) => updateQuestionnaire('technicalShots', value)} />
      </div>

      <button className="primary-button" type="submit">
        <Plus size={18} />
        Create my account
      </button>
    </form>
  );
}

function RangeField({ label, value, onChange }) {
  return (
    <label className="range-field">
      <span>{label}</span>
      <input type="range" min="1" max="8" value={value} onChange={(event) => onChange(Number(event.target.value))} />
      <strong>{value}</strong>
    </label>
  );
}

function Dashboard({ players, matches, currentPlayer, token, runAction, onSelectMatch }) {
  return (
    <div className="dashboard-grid">
      <section className="panel match-list-panel">
        <div className="panel-heading">
          <div>
            <p className="eyebrow">Matches</p>
            <h2>My latest matches</h2>
          </div>
          <span className="count-badge">{matches.length}</span>
        </div>
        <div className="match-list">
          {matches.length === 0 && <EmptyState text="No matches yet." />}
          {matches.map((match) => (
            <button className="match-item" key={match.id} onClick={() => onSelectMatch(match.id)}>
              <div>
                <strong>Match #{match.id}</strong>
                <span>{teamLabel(match.teamA)} vs {teamLabel(match.teamB)}</span>
              </div>
              <StatusPill status={match.status} />
            </button>
          ))}
        </div>
      </section>

      <section className="panel">
        <div className="panel-heading">
          <div>
            <p className="eyebrow">Nouveau</p>
            <h2>Create a match</h2>
          </div>
          <Plus size={20} />
        </div>
        <CreateMatchForm
          players={players}
          currentPlayer={currentPlayer}
          token={token}
          runAction={runAction}
          onCreated={(match) => onSelectMatch(match.id)}
        />
      </section>
    </div>
  );
}

function CreateMatchForm({ players, currentPlayer, token, runAction, onCreated }) {
  const [teamA, setTeamA] = useState([currentPlayer.id, '']);
  const [teamB, setTeamB] = useState(['', '']);
  const [scheduledAt, setScheduledAt] = useState('');

  async function submit(event) {
    event.preventDefault();
    const result = await runAction(async () => {
      return apiRequest('/api/matches', {
        token,
        method: 'POST',
        body: {
          teamA: teamA.map(Number),
          teamB: teamB.map(Number),
          scheduledAt: scheduledAt || null,
        },
      });
    }, 'Match created.');

    if (result) {
      onCreated(asMatch(result));
    }
  }

  return (
    <form className="form-stack compact-form" onSubmit={submit}>
      <TeamSelect title="Team A" values={teamA} players={players} onChange={setTeamA} />
      <TeamSelect title="Team B" values={teamB} players={players} onChange={setTeamB} />
      <label>
        Date
        <input type="datetime-local" value={scheduledAt} onChange={(event) => setScheduledAt(event.target.value)} />
      </label>
      <button className="primary-button" type="submit" disabled={players.length < 4}>
        <Plus size={18} />
        Save
      </button>
      {players.length < 4 && <p className="hint">At least 4 player accounts are required to create a match.</p>}
    </form>
  );
}

function TeamSelect({ title, values, players, onChange }) {
  return (
    <fieldset className="team-select">
      <legend>{title}</legend>
      {values.map((value, index) => (
        <select
          key={index}
          value={value}
          onChange={(event) => {
            const next = [...values];
            next[index] = event.target.value;
            onChange(next);
          }}
          required
        >
          <option value="">Choose a player</option>
          {players.map((player) => (
            <option key={player.id} value={player.id}>
              {player.fullName} · N{player.level}
            </option>
          ))}
        </select>
      ))}
    </fieldset>
  );
}

function MatchDetail({ match, token, runAction, onBack }) {
  const currentProposal = match.currentScoreProposal;

  return (
    <div className="detail-grid">
      <section className="panel">
        <button className="ghost-button" onClick={onBack}>Back</button>
        <div className="match-teams">
          <TeamBlock title="Team A" players={match.teamA} />
          <div className="versus">vs</div>
          <TeamBlock title="Team B" players={match.teamB} />
        </div>

        <div className="actions-row">
          {match.status !== 'validated' && (
            <button
              className="secondary-button"
              onClick={() => runAction(
                () => apiRequest(`/api/matches/${match.id}/finish`, { token, method: 'POST' }),
                'Email invitations sent.'
              )}
            >
              <Mail size={18} />
              Invite score entry
            </button>
          )}
          {match.status === 'validated' && (
            <div className="validated-box">
              <ShieldCheck size={18} />
              Score validated by all 4 players
            </div>
          )}
        </div>
      </section>

      <section className="panel">
        <div className="panel-heading">
          <div>
            <p className="eyebrow">Current score</p>
            <h2>{currentProposal ? formatSets(currentProposal.sets) : 'No score'}</h2>
          </div>
          {currentProposal && <span className="count-badge">{currentProposal.approvedCount}/{currentProposal.requiredCount}</span>}
        </div>

        {currentProposal ? (
          <>
            <ValidationList proposal={currentProposal} />
            {match.status !== 'validated' && (
              <div className="actions-row">
                <button
                  className="primary-button"
                  onClick={() => runAction(
                    () => apiRequest(`/api/matches/${match.id}/score-proposals/current/approve`, { token, method: 'POST' }),
                    'Validation saved.'
                  )}
                >
                  <Check size={18} />
                  Approve
                </button>
                <RejectButton matchId={match.id} token={token} runAction={runAction} />
              </div>
            )}
          </>
        ) : (
          <EmptyState text="The first submitted score becomes the current score." />
        )}
      </section>

      {match.status !== 'validated' && (
        <section className="panel">
          <div className="panel-heading">
            <div>
              <p className="eyebrow">Correction</p>
              <h2>Propose a score</h2>
            </div>
            <CircleAlert size={20} />
          </div>
          <ScoreForm
            matchId={match.id}
            token={token}
            runAction={runAction}
            label={currentProposal ? 'Propose the change' : 'Enter the score'}
          />
        </section>
      )}

      <section className="panel history-panel">
        <div className="panel-heading">
          <div>
            <p className="eyebrow">Historique</p>
            <h2>Score proposals</h2>
          </div>
        </div>
        <div className="proposal-list">
          {(match.scoreProposals || []).map((proposal) => (
            <div className="proposal-item" key={proposal.id}>
              <strong>{formatSets(proposal.sets)}</strong>
              <span>By {proposal.proposedBy.fullName}</span>
              <small>{proposal.current ? 'Current score' : 'Replaced'}</small>
            </div>
          ))}
          {(match.scoreProposals || []).length === 0 && <EmptyState text="No proposals." />}
        </div>
      </section>
    </div>
  );
}

function ScoreForm({ matchId, token, runAction, label }) {
  const [sets, setSets] = useState([
    { teamA: 6, teamB: 4 },
    { teamA: 6, teamB: 4 },
    { teamA: 0, teamB: 0 },
  ]);

  function updateSet(index, side, value) {
    setSets((current) => current.map((set, setIndex) => (
      setIndex === index ? { ...set, [side]: Number(value) } : set
    )));
  }

  async function submit(event) {
    event.preventDefault();
    await runAction(
      () => apiRequest(`/api/matches/${matchId}/score-proposals`, {
        token,
        method: 'POST',
        body: { sets },
      }),
      'Score proposed.'
    );
  }

  return (
    <form className="form-stack compact-form" onSubmit={submit}>
      <div className="score-grid">
        <span></span>
        <strong>Team A</strong>
        <strong>Team B</strong>
        {sets.map((set, index) => (
          <ScoreSetRow key={index} index={index} set={set} updateSet={updateSet} />
        ))}
      </div>
      <button className="primary-button" type="submit">
        <Check size={18} />
        {label}
      </button>
    </form>
  );
}

function ScoreSetRow({ index, set, updateSet }) {
  return (
    <>
      <span>Set {index + 1}</span>
      <input type="number" min="0" max="7" value={set.teamA} onChange={(event) => updateSet(index, 'teamA', event.target.value)} />
      <input type="number" min="0" max="7" value={set.teamB} onChange={(event) => updateSet(index, 'teamB', event.target.value)} />
    </>
  );
}

function RejectButton({ matchId, token, runAction }) {
  const [open, setOpen] = useState(false);
  const [comment, setComment] = useState('');

  if (!open) {
    return (
      <button className="danger-button" onClick={() => setOpen(true)}>
        <X size={18} />
        Reject
      </button>
    );
  }

  return (
    <div className="reject-box">
      <input value={comment} onChange={(event) => setComment(event.target.value)} placeholder="Short reason" />
      <button
        className="danger-button"
        onClick={() => runAction(
          () => apiRequest(`/api/matches/${matchId}/score-proposals/current/reject`, {
            token,
            method: 'POST',
            body: { comment },
          }),
          'Rejection saved.'
        )}
      >
        <X size={18} />
        Confirm
      </button>
    </div>
  );
}

function ValidationList({ proposal }) {
  return (
    <div className="validation-list">
      {proposal.validations.map((validation) => (
        <div className={`validation-item ${validation.decision}`} key={validation.player.id}>
          {validation.decision === 'approved' ? <Check size={16} /> : <X size={16} />}
          <span>{validation.player.fullName}</span>
          {validation.comment && <small>{validation.comment}</small>}
        </div>
      ))}
    </div>
  );
}

function TeamBlock({ title, players }) {
  return (
    <div className="team-block">
      <span>{title}</span>
      {players.map((player) => (
        <strong key={player.id}>{player.fullName} <em>N{player.level}</em></strong>
      ))}
    </div>
  );
}

function StatusPill({ status, loading }) {
  if (loading) {
    return <span className="status-pill muted"><Clock size={14} />Loading</span>;
  }

  if (!status) return null;

  return <span className={`status-pill ${status}`}><Clock size={14} />{statusLabels[status] || status}</span>;
}

function Notice({ type, text }) {
  return (
    <div className={`notice ${type}`}>
      {type === 'error' ? <CircleAlert size={18} /> : <Check size={18} />}
      {text}
    </div>
  );
}

function EmptyState({ text }) {
  return <div className="empty-state">{text}</div>;
}

function teamLabel(players) {
  return players.map((player) => player.firstName).join(' / ');
}

function formatSets(sets) {
  return sets.map((set) => `${set.teamA}-${set.teamB}`).join('  ');
}

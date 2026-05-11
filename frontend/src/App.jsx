import { useEffect, useState } from 'react';
import { apiRequest } from './api.js';
import { AuthenticatedShell } from './components/AuthenticatedShell.jsx';
import { AuthScreen } from './features/auth/AuthScreen.jsx';
import { Dashboard } from './features/dashboard/Dashboard.jsx';
import { MatchDetail } from './features/matches/MatchDetail.jsx';
import { asCollection, asMatch, asPlayer } from './lib/hydra.js';
import { readInitialMatchId } from './lib/matchRoute.js';

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

  const headerTitle = selectedMatch ? `Match n°${selectedMatch.id}` : 'Tableau de bord';

  return (
    <AuthenticatedShell
      player={player}
      headerTitle={headerTitle}
      loading={loading}
      error={error}
      message={message}
      statusPillStatus={selectedMatch?.status}
      onDashboard={() => setSelectedMatchId(null)}
      onRefresh={refreshData}
      onLogout={logout}
    >
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
    </AuthenticatedShell>
  );
}

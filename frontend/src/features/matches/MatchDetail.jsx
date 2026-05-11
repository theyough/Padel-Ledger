import { Check, CircleAlert, Mail, ShieldCheck } from 'lucide-react';
import { apiRequest } from '../../api.js';
import { EmptyState } from '../../components/EmptyState.jsx';
import { formatSets } from '../../lib/matchDisplay.js';
import { RejectButton } from './RejectButton.jsx';
import { ScoreForm } from './ScoreForm.jsx';
import { TeamBlock } from './TeamBlock.jsx';
import { ValidationList } from './ValidationList.jsx';

export function MatchDetail({ match, token, runAction, onBack }) {
  const currentProposal = match.currentScoreProposal;

  return (
    <div className="detail-grid">
      <section className="panel">
        <button type="button" className="ghost-button" onClick={onBack}>Retour</button>
        <div className="match-teams">
          <TeamBlock title="Équipe A" players={match.teamA} />
          <div className="versus">contre</div>
          <TeamBlock title="Équipe B" players={match.teamB} />
        </div>

        <div className="actions-row">
          {match.status !== 'validated' && (
            <button
              type="button"
              className="secondary-button"
              onClick={() => runAction(
                () => apiRequest(`/api/matches/${match.id}/finish`, { token, method: 'POST' }),
                'Les invitations par e-mail ont été envoyées.'
              )}
            >
              <Mail size={18} />
              Inviter à saisir le score
            </button>
          )}
          {match.status === 'validated' && (
            <div className="validated-box">
              <ShieldCheck size={18} />
              Score validé par les 4 joueurs
            </div>
          )}
        </div>
      </section>

      <section className="panel">
        <div className="panel-heading">
          <div>
            <p className="eyebrow">Score actuel</p>
            <h2>{currentProposal ? formatSets(currentProposal.sets) : 'Pas de score'}</h2>
          </div>
          {currentProposal && <span className="count-badge">{currentProposal.approvedCount}/{currentProposal.requiredCount}</span>}
        </div>

        {currentProposal ? (
          <>
            <ValidationList proposal={currentProposal} />
            {match.status !== 'validated' && (
              <div className="actions-row">
                <button
                  type="button"
                  className="primary-button"
                  onClick={() => runAction(
                    () => apiRequest(`/api/matches/${match.id}/score-proposals/current/approve`, { token, method: 'POST' }),
                    'Validation enregistrée.'
                  )}
                >
                  <Check size={18} />
                  Valider
                </button>
                <RejectButton matchId={match.id} token={token} runAction={runAction} />
              </div>
            )}
          </>
        ) : (
          <EmptyState text="Le premier score soumis devient le score actuel." />
        )}
      </section>

      {match.status !== 'validated' && (
        <section className="panel">
          <div className="panel-heading">
            <div>
              <p className="eyebrow">Correction</p>
              <h2>Proposer un score</h2>
            </div>
            <CircleAlert size={20} />
          </div>
          <ScoreForm
            matchId={match.id}
            token={token}
            runAction={runAction}
            label={currentProposal ? 'Proposer la modification' : 'Saisir le score'}
          />
        </section>
      )}

      <section className="panel history-panel">
        <div className="panel-heading">
          <div>
            <p className="eyebrow">Historique</p>
            <h2>Propositions de score</h2>
          </div>
        </div>
        <div className="proposal-list">
          {(match.scoreProposals || []).map((proposal) => (
            <div className="proposal-item" key={proposal.id}>
              <strong>{formatSets(proposal.sets)}</strong>
              <span>Par {proposal.proposedBy.fullName}</span>
              <small>{proposal.current ? 'Score actuel' : 'Remplacé'}</small>
            </div>
          ))}
          {(match.scoreProposals || []).length === 0 && <EmptyState text="Aucune proposition." />}
        </div>
      </section>
    </div>
  );
}

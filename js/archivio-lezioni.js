const { createApp, ref, onMounted } = Vue;

// Percorso relativo al posto di indirizzo a localhost
const API_BASE = 'api';

createApp({
  setup() {

    /* stato */
    const edizione     = ref(null);
    const lezioni      = ref([]);
    const partecipanti = ref([]);

    const loadingEdizione = ref(false);
    const loadingLezioni  = ref(false);
    const erroreEdizione  = ref('');
    const erroreLezioni   = ref('');

    const form       = ref({ lezione_id: '', partecipante_id: '', presente: true });
    const invio      = ref(false);
    const msgSuccess = ref('');
    const msgErrore  = ref('');

    /* utilità */
    function formatData(iso) {
      if (!iso) return '';
      const [y, m, d] = iso.split('-');
      return `${d}/${m}/${y}`;
    }

    /* fetch edizione */
    async function caricaEdizione() {
      loadingEdizione.value = true;
      erroreEdizione.value  = '';
      try {
        const response = await fetch(`${API_BASE}/edizione.php`);
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        edizione.value = await response.json();
      } catch {
        erroreEdizione.value = "Impossibile caricare i dati dell'edizione.";
      } finally {
        loadingEdizione.value = false;
      }
    }

    /* fetch lezioni */
    async function caricaLezioni() {
      loadingLezioni.value = true;
      erroreLezioni.value  = '';
      try {
        const response = await fetch(`${API_BASE}/lezioni.php`);
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        lezioni.value = await response.json();
      } catch {
        erroreLezioni.value = 'Impossibile caricare le lezioni.';
      } finally {
        loadingLezioni.value = false;
      }
    }

    /* fetch partecipanti */
    async function caricaPartecipanti() {
      try {
        const response = await fetch(`${API_BASE}/partecipanti.php`);
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        partecipanti.value = await response.json();
      } catch {
        // In questo mdoo la select resta semplicemente vuota
      }
    }

    /* registra presenza */
    async function registraPresenza() {
      msgSuccess.value = '';
      msgErrore.value  = '';
      invio.value      = true;

      try {
        const response = await fetch(`${API_BASE}/presenze.php`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            lezione_id:      form.value.lezione_id,
            partecipante_id: form.value.partecipante_id,
            presente:        form.value.presente,
          }),
        });

        const data = await response.json();

        if (!response.ok) {
          msgErrore.value = data.errore || 'Errore durante il salvataggio.';
        } else {
          msgSuccess.value = data.messaggio || 'Presenza registrata.';
          form.value = { lezione_id: '', partecipante_id: '', presente: true };
          await Promise.all([caricaLezioni(), caricaEdizione()]);
        }
      } catch {
        msgErrore.value = 'Errore di connessione al server.';
      } finally {
        invio.value = false;
      }
    }

    /* mount */
    onMounted(() => {
      Promise.all([caricaEdizione(), caricaLezioni(), caricaPartecipanti()]);
    });

    return {
      edizione, lezioni, partecipanti,
      loadingEdizione, loadingLezioni,
      erroreEdizione, erroreLezioni,
      form, invio, msgSuccess, msgErrore,
      formatData, registraPresenza,
    };
  }
}).mount('#app');

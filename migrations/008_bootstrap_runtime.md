# Bootstrap runtime mitigation

> Antecedente histórico: la fase [019](019_bootstrap3_retirement.md) retira
> Bootstrap 3 del manifiesto/sincronizador y bloquea por HTTP sus copias,
> runtime reducido y fixture anterior. Las cargas y comprobaciones siguientes
> describen la mitigación aplicada en el paso 50, no el runtime actual.

PGCV retains Bootstrap 3.4.1 CSS for AdminLTE 2 compatibility and loads
`dist/js/bootstrap-pgcv.js` from both shared script templates. Run
`npm ci --ignore-scripts` and `npm run sync:frontend` to reproduce this file.
The generator concatenates the original MIT-licensed transition, alert,
carousel, collapse, dropdown, modal, scrollspy, tab and affix modules.

The unused Button, Tooltip and Popover plugins are excluded. This removes
the runtime components affected by
[CVE-2024-6485](https://www.herodevs.com/vulnerability-directory/cve-2024-6485)
and [CVE-2025-1647](https://www.herodevs.com/vulnerability-directory/cve-2025-1647).
The Bootstrap directory's `.htaccess` blocks direct HTTP access to the full
upstream bundles and the three excluded source modules on Apache 2.4.
Other hosting servers must implement the equivalent access rule.

Do not load the upstream complete JavaScript bundle or initialize
`.button()`, `.tooltip()` or `.popover()` in new screens. Bootstrap-style
ordinary buttons, native `title` attributes and Chart.js tooltips are
unaffected by removing these plugins.

Check `tests/fixtures/bootstrap_runtime.html` through Apache: eight checks
verify plugin exclusion and the retained interactive behavior without
database access or email. Existing `npm test` checks remain separate.

`npm audit --json --ignore-scripts` on October 2, 2026 reports one moderate
package (Bootstrap) with these two advisories. It evaluates the installed
package version, not the reduced served runtime, so the finding remains.
This is a targeted mitigation, not an upstream security update or a clean
audit. Bootstrap 3 is end-of-life; migration of Bootstrap and AdminLTE to
supported versions remains outstanding. Other manually copied libraries
are outside this npm audit's coverage.

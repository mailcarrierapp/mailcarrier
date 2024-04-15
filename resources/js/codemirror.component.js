import {
  history,
  indentWithTab
} from "@codemirror/commands";
import { html } from "@codemirror/lang-html";
import { Compartment, EditorState } from '@codemirror/state';
import { EditorView, highlightActiveLineGutter, keymap, lineNumbers } from "@codemirror/view";
import { dracula } from 'thememirror';

// Adapted from https://github.com/dotswan/filament-code-editor
const CodeEditorAlpinePlugin = (Alpine) => {
  Alpine.data('codeEditorFormComponent', ({ state, isReadOnly, language = 'html' }) => ({
    state,
    editor: undefined,
    themeConfig: undefined,
    languageConfig: undefined,
    isReadOnly: false,

    init() {
      this.isReadOnly = isReadOnly;
      this.themeConfig = new Compartment();
      this.languageConfig = new Compartment();
      this.render();

      // Needed for programmatic updates from Livewire (e.g. form fill) to the component
      this.$watch('state', (value) => {
        if (this.editor.state.doc.toString() !== value) {
          this.editor.dispatch({
            changes: { from: 0, to: this.editor.state.doc.length, insert: value }
          });
        }
      });
    },

    render() {
      this.editor = new EditorView({
        parent: this.$refs.codeEditor,
        state: EditorState.create({
          doc: this.state,
          autofocus: true,
          indentWithTabs: true,
          smartIndent: true,
          lineNumbers: true,
          matchBrackets: true,
          tabSize: 2,
          styleSelectedText: true,
          extensions: [
            keymap.of([indentWithTab]),
            this.languageConfig.of(language === 'json' ? json() : html()),
            this.themeConfig.of([dracula]),
            EditorView.lineWrapping,
            EditorState.readOnly.of(this.isReadOnly),
            lineNumbers(),
            history(),
            highlightActiveLineGutter(),
            EditorView.updateListener.of((v) => {
              if (v.docChanged) {
                this.state = v.state.doc.toString();
                this.$wire.$commit();
              }
            }),
          ],
        }),
      });
    },
  }));
}

document.addEventListener('alpine:init', () => {
  window.Alpine.plugin(CodeEditorAlpinePlugin);
});

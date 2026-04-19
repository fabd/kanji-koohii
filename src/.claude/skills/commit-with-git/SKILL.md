---
name: commit-with-git
description: Commits ONLY the staged changes with git.
disable-model-invocation: true
---

Commit **staged changes only** with git.

Checklist before you use `git commit`:

- it's not necessary to cd to the root of the project, you can run git commit from `src/`
- stop if there are no staged changes, tell user they should stage changes first
- double-check the current branch name, because the user may have just changed it
- if the branch name is `develop`, determine a meaningful branch name (see Naming Branch below), 
then go ahead and **rename** the current branch (do not use checkout -b)

There is no need for confirmation in those steps. The user is free to edit the commit
description and even rename the branch before pushing it.

## Naming Branch

Use the following pattern for branch names:

<scope>/<kebab-case-meaningful-title>

Scope can be:
- feature
- chore
- refactor
- docs

Pick a scope which fits the majority of the staged changes.

Example branch names:

```
feature/some-cool-feature
refactor/fix-login-not-working
docs/learn-more-page
```

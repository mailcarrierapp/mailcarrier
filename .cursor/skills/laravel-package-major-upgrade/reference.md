# Testbench vs Laravel (reference)

Orchestra Testbench majors **do not** track Laravel’s version number. Recent pairs are often:

| Laravel major | Typical Testbench major (verify) |
|---------------|-----------------------------------|
| 10            | 8.*                               |
| 11            | 9.*                               |
| 12            | 10.*                              |
| 13            | 11.*                              |

Always confirm against the Testbench release or README for the exact Laravel range before shipping. The skill’s default rule remains: **add Testbench major = previous repo max + 1** when adding the next supported Laravel major, then validate in CI.

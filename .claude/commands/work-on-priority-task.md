# Priority  Task Command

You are tasked with finding and working on the highest priority  task assigned to the current user in Jira.

Follow these steps:

1. **Get current user information**:
   - Use the `mcp__atlassian__atlassianUserInfo` tool to get the current user's account ID

2. **Get accessible Atlassian resources**:
   - Use the `mcp__atlassian__getAccessibleAtlassianResources` tool to get the cloud ID

3. **Search for assigned tasks**:
   - Use the `mcp__atlassian__searchJiraIssuesUsingJql` tool with the following JQL query:
   - `assignee = currentUser() AND resolution = Unresolved ORDER BY priority DESC, created DESC`
   - Request fields: `summary`, `description`, `status`, `issuetype`, `priority`, `labels`, `created`

4. **Identify the highest priority task**:
   - The first result will be the highest priority task
   - Display the task information to the user including:
     - Issue key
     - Summary
     - Description
     - Priority
     - Status
     - Labels

5. **Determine if it's a database optimization task**:
   - Check if the task is related to database optimization by looking for keywords in:
     - Summary (title)
     - Description
     - Labels
   - Keywords to look for (case-insensitive):
     - "database", "db", "query", "queries", "SQL", "optimization", "optimize", "performance", "n+1", "slow query", "index", "indexing", "ORM", "database access", "query performance", "database performance"

6. **If it's a database optimization task**:
   - Inform the user that this is a database optimization task
   - Use the `Task` tool to invoke the `sql-query-optimizer` agent with:
     - subagent_type: `sql-query-optimizer`
     - description: "Optimize database queries"
     - prompt: "I need help with the following database optimization task from Jira:\n\nIssue: [ISSUE-KEY]\nTitle: [TITLE]\n\nDescription:\n[DESCRIPTION]\n\nPlease analyze the codebase for potential database query issues related to this task, identify N+1 query problems, make a plan with optimizations and then perform the plan"

7. **If it's NOT a database optimization task**:
   - Inform the user that the highest priority task is not related to database optimization
   - Ask if they would like to:
     - Work on this task anyway
     - Search for the next database-related task in their queue
     - Do something else

**Important**: Be thorough in your analysis and provide clear, actionable information to the user.

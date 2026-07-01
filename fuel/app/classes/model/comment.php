<?php
class Model_Comment extends Model
{
    // Get all comments received by an employee (with author name)
    public static function get_received_by_employee($employee_id)
    {
        return DB::query(
            "SELECT c.id AS comment_id, c.author_id, c.content, c.create_time, e.name AS author_name
             FROM comments c
             JOIN employees e ON c.author_id = e.id
             WHERE c.receiver_id = :id
             ORDER BY c.create_time DESC"
        )->param('id', $employee_id)->execute()->as_array();
    }

    // Insert a new comment and return the saved comment data (with author name)
    public static function post($author_id, $receiver_id, $content)
    {
        list($insert_id) = DB::insert('comments')
            ->columns(['author_id', 'receiver_id', 'content'])
            ->values([$author_id, $receiver_id, $content])
            ->execute();

        $row = DB::query(
            "SELECT c.id AS comment_id, c.author_id, c.content, c.create_time, e.name AS author_name
             FROM comments c
             JOIN employees e ON c.author_id = e.id
             WHERE c.id = :id"
        )->param('id', $insert_id)->execute()->current();

        return $row;
    }

    // Update a comment's content (only if author matches)
    public static function update($comment_id, $author_id, $new_content)
    {
        $affected = DB::update('comments')
            ->set(['content' => $new_content])
            ->where('id', '=', $comment_id)
            ->where('author_id', '=', $author_id)
            ->execute();
        return $affected > 0;
    }

    // Delete a comment (only if author matches)
    public static function delete($comment_id, $author_id)
    {
        $affected = DB::delete('comments')
            ->where('id', '=', $comment_id)
            ->where('author_id', '=', $author_id)
            ->execute();
        return $affected > 0;
    }
}
